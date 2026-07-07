/**
 * MySQL data client for the admin panel.
 * Provides collection/document query helpers backed by Laravel /admin-data routes.
 */
(function () {
    const apiBase = document.querySelector('meta[name="kweek-mysql-api"]')?.getAttribute('content') || '/admin-data';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function apiRequest(path, options) {
        options = options || {};
        const baseHeaders = {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        };
        if (!(options.body instanceof FormData)) {
            baseHeaders['Content-Type'] = 'application/json';
        }
        const headers = Object.assign({}, baseHeaders, options.headers || {});
        const fetchOptions = Object.assign({ credentials: 'same-origin' }, options);
        fetchOptions.headers = headers;
        return fetch(apiBase + path, fetchOptions)
            .then((response) => response.json());
    }

    function makeTimestamp(date) {
        date = date || new Date();
        const seconds = Math.floor(date.getTime() / 1000);
        const nanoseconds = (date.getTime() % 1000) * 1000000;
        return {
            seconds,
            nanoseconds,
            _seconds: seconds,
            _nanoseconds: nanoseconds,
            toDate: function () { return new Date(date.getTime()); },
            toMillis: function () { return date.getTime(); },
        };
    }

    function deepConvertTimestamps(obj) {
        if (obj === null || obj === undefined) return obj;
        if (Array.isArray(obj)) {
            return obj.map(deepConvertTimestamps);
        }
        if (typeof obj === 'object') {
            if ((obj.seconds !== undefined || obj._seconds !== undefined) &&
                (obj.nanoseconds !== undefined || obj._nanoseconds !== undefined)) {
                const secs = obj.seconds !== undefined ? obj.seconds : obj._seconds;
                return makeTimestamp(new Date(secs * 1000));
            }

            for (let key in obj) {
                if (obj.hasOwnProperty(key)) {
                    const val = obj[key];
                    if (typeof val === 'string' && val.trim() !== '') {
                        const isDateKey = /date|createdat|updatedat|expiresat|expiry/i.test(key);
                        const isDateString = /^\d{4}-\d{2}-\d{2}/.test(val) || /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/.test(val);
                        if (isDateKey || isDateString) {
                            const parsed = Date.parse(val);
                            if (!isNaN(parsed)) {
                                obj[key] = makeTimestamp(new Date(parsed));
                            }
                        }
                    } else if (typeof val === 'object') {
                        obj[key] = deepConvertTimestamps(val);
                    }
                }
            }
        }
        return obj;
    }

    function makeSnapshot(id, data, collectionName) {
        const exists = data != null;
        const convertedData = exists ? deepConvertTimestamps(data) : {};
        return {
            id: id,
            exists: exists,
            ref: makeDocRef(collectionName, id),
            data: function () { return convertedData; },
            get: function (field) { return convertedData[field]; },
        };
    }

    function makeQuerySnapshot(docs, collectionName) {
        const mapped = docs.map(function (row) {
            const id = row.id || row._id || '';
            const convertedRow = deepConvertTimestamps(row);
            return {
                id: id,
                exists: true,
                ref: makeDocRef(collectionName, id),
                data: function () { return convertedRow; },
                get: function (field) { return convertedRow[field]; },
            };
        });
        return {
            docs: mapped,
            empty: mapped.length === 0,
            size: mapped.length,
            forEach: function (cb) { mapped.forEach(cb); },
            docChanges: function () {
                return mapped.map(function (doc, index) {
                    return { type: 'added', doc: doc, newIndex: index, oldIndex: -1 };
                });
            },
        };
    }

    function buildFilters(conditions) {
        return conditions.map(function (item) {
            return { field: item[0], op: item[1], value: item[2] };
        });
    }

    function makeDocRef(collectionName, docId) {
        const ref = {
            id: docId,
            path: collectionName + '/' + docId,
            get: function () {
                return apiRequest('/document/' + encodeURIComponent(collectionName) + '/' + encodeURIComponent(docId))
                    .then(function (json) { return makeSnapshot(docId, json.data || null, collectionName); });
            },
            set: function (data) {
                return apiRequest('/upsert', {
                    method: 'POST',
                    body: JSON.stringify({ collection: collectionName, id: docId, data: data, merge: false }),
                });
            },
            update: function (data) {
                return apiRequest('/upsert', {
                    method: 'POST',
                    body: JSON.stringify({ collection: collectionName, id: docId, data: data, merge: true }),
                });
            },
            delete: function () {
                return apiRequest('/document/' + encodeURIComponent(collectionName) + '/' + encodeURIComponent(docId), {
                    method: 'DELETE',
                });
            },
            onSnapshot: function (callback) {
                return ref.get().then(function (snap) { callback(snap); });
            },
            collection: function (sub) {
                return makeCollectionRef(collectionName + '/' + docId + '/' + sub);
            },
        };
        return ref;
    }

    function makeQuery(collectionName, conditions, options) {
        conditions = conditions || [];
        options = options || {};
        const query = {
            where: function (field, op, value) {
                const next = conditions.slice();
                next.push([field, op, value]);
                return makeQuery(collectionName, next, options);
            },
            orderBy: function (field, direction) {
                const nextOptions = Object.assign({}, options, { orderBy: field, orderDir: direction || 'asc' });
                return makeQuery(collectionName, conditions, nextOptions);
            },
            limit: function (count) {
                const nextOptions = Object.assign({}, options, { limit: count });
                return makeQuery(collectionName, conditions, nextOptions);
            },
            startAt: function (value) {
                const nextOptions = Object.assign({}, options, { startAt: value });
                return makeQuery(collectionName, conditions, nextOptions);
            },
            endAt: function (value) {
                const nextOptions = Object.assign({}, options, { endAt: value });
                return makeQuery(collectionName, conditions, nextOptions);
            },
            get: function () {
                return apiRequest('/query', {
                    method: 'POST',
                    body: JSON.stringify({
                        collection: collectionName,
                        filters: buildFilters(conditions),
                        limit: options.limit || 500,
                        orderBy: options.orderBy || null,
                        orderDir: options.orderDir || 'desc',
                        startAt: options.startAt || null,
                        endAt: options.endAt || null,
                    }),
                }).then(function (json) {
                    const rows = Array.isArray(json.data) ? json.data : [];
                    return makeQuerySnapshot(rows, collectionName);
                });
            },
            onSnapshot: function (callback, errorCallback) {
                let knownIds = new Set();
                const poll = function () {
                    query.get().then(function (snapshot) {
                        const changes = snapshot.docs.map(function (doc, index) {
                            const type = knownIds.has(doc.id) ? 'modified' : 'added';
                            knownIds.add(doc.id);
                            return { type: type, doc: doc, newIndex: index, oldIndex: -1 };
                        });
                        callback({
                            docs: snapshot.docs,
                            docChanges: function () { return changes; },
                            forEach: snapshot.forEach,
                            empty: snapshot.empty,
                            size: snapshot.size,
                        });
                    }).catch(function (error) {
                        if (typeof errorCallback === 'function') {
                            errorCallback(error);
                        }
                    });
                };
                poll();
                const timer = setInterval(poll, 15000);
                return function () { clearInterval(timer); };
            },
        };
        return query;
    }

    function makeCollectionRef(collectionName) {
        return {
            doc: function (id) {
                if (!id) {
                    id = 'doc_' + Date.now() + '_' + Math.random().toString(36).slice(2, 9);
                }
                return makeDocRef(collectionName, id);
            },
            where: function (field, op, value) {
                return makeQuery(collectionName, [[field, op, value]], {});
            },
            orderBy: function (field, direction) {
                return makeQuery(collectionName, [], { orderBy: field, orderDir: direction || 'asc' });
            },
            limit: function (count) {
                return makeQuery(collectionName, [], { limit: count });
            },
            get: function () {
                return makeQuery(collectionName, [], {}).get();
            },
            add: function (data) {
                const id = data.id || ('doc_' + Date.now() + '_' + Math.random().toString(36).slice(2, 9));
                return apiRequest('/upsert', {
                    method: 'POST',
                    body: JSON.stringify({ collection: collectionName, id: id, data: data, merge: false }),
                }).then(function () { return makeDocRef(collectionName, id); });
            },
            onSnapshot: function (callback, errorCallback) {
                return makeQuery(collectionName, [], {}).onSnapshot(callback, errorCallback);
            },
        };
    }

    const kweekDbApi = {
        collection: function (name) { return makeCollectionRef(name); },
        Timestamp: {
            fromDate: makeTimestamp,
            now: function () { return makeTimestamp(new Date()); },
        },
        FieldValue: {
            serverTimestamp: function () { return new Date().toISOString(); },
        },
        GeoPoint: function (latitude, longitude) {
            return { latitude: latitude, longitude: longitude, _lat: latitude, _long: longitude };
        },
    };

    function kweekDb() {
        return kweekDbApi;
    }
    kweekDb.FieldValue = kweekDbApi.FieldValue;
    kweekDb.GeoPoint = kweekDbApi.GeoPoint;
    kweekDb.Timestamp = kweekDbApi.Timestamp;

    function makeUploadTask(promise) {
        let onProgress = null;
        let onError = null;
        let onComplete = null;
        let completed = false;
        let taskError = null;

        const task = {
            on: function (event, progress, error, complete) {
                if (event === 'state_changed') {
                    onProgress = progress;
                    onError = error;
                    onComplete = complete;

                    if (completed) {
                        if (taskError) {
                            if (onError) onError(taskError);
                        } else {
                            if (onComplete) onComplete();
                        }
                    }
                }
            },
            then: function (onSuccess, onFailure) {
                return promise.then(onSuccess, onFailure);
            },
            catch: function (onFailure) {
                return promise.catch(onFailure);
            },
            snapshot: {
                bytesTransferred: 0,
                totalBytes: 100,
                ref: {
                    getDownloadURL: function () {
                        return promise.then(function (res) {
                            return res.ref.getDownloadURL();
                        });
                    }
                }
            },
            ref: {
                getDownloadURL: function () {
                    return promise.then(function (res) {
                        return res.ref.getDownloadURL();
                    });
                }
            }
        };

        promise.then(
            function (res) {
                completed = true;
                task.snapshot.bytesTransferred = 100;
                task.snapshot.totalBytes = 100;
                task.snapshot.ref = res.ref;
                task.ref = res.ref;

                if (onProgress) {
                    try { onProgress({ bytesTransferred: 100, totalBytes: 100 }); } catch (e) {}
                }
                if (onComplete) {
                    try { onComplete(); } catch (e) {}
                }
            },
            function (err) {
                completed = true;
                taskError = err;
                if (onError) {
                    try { onError(err); } catch (e) {}
                }
            }
        );

        return task;
    }

    function makeFileRef(path) {
        return {
            child: function (name) {
                const childPath = (path ? path + '/' : '') + name;
                return makeFileRef(childPath);
            },
            putString: function (data, format, metadata) {
                const contentType = (metadata && metadata.contentType) || 'application/octet-stream';
                const blob = (format === 'base64')
                    ? (function () {
                        const raw = data.replace(/^data:[^;]+;base64,/, '');
                        const bytes = atob(raw);
                        const arr = new Uint8Array(bytes.length);
                        for (let i = 0; i < bytes.length; i++) arr[i] = bytes.charCodeAt(i);
                        return new Blob([arr], { type: contentType });
                    })()
                    : new Blob([data], { type: contentType });

                let filename = 'upload';
                let directory = path || 'images';
                const parts = (path || '').split('/');
                if (parts.length > 1) {
                    filename = parts.pop();
                    directory = parts.join('/');
                } else if (parts.length === 1 && parts[0] !== 'images' && parts[0] !== '') {
                    filename = parts[0];
                    directory = 'images';
                }
                if (filename.indexOf('.') === -1) {
                    const ext = contentType.split('/')[1];
                    if (ext && ext !== 'octet-stream') {
                        filename += '.' + (ext === 'jpeg' ? 'jpg' : ext);
                    }
                }

                const file = new File([blob], filename, { type: contentType });
                const formData = new FormData();
                formData.append('file', file);
                formData.append('directory', directory);

                const promise = apiRequest('/upload', { method: 'POST', body: formData, headers: {} })
                    .then(function (json) {
                        const url = json.url;
                        return {
                            ref: {
                                getDownloadURL: function () { return Promise.resolve(url); },
                            },
                        };
                    });
                return makeUploadTask(promise);
            },
            put: function (file) {
                let filename = file.name;
                let directory = path || 'images';
                const parts = (path || '').split('/');
                if (parts.length > 1) {
                    filename = parts.pop();
                    directory = parts.join('/');
                } else if (parts.length === 1 && parts[0] !== 'images' && parts[0] !== '') {
                    filename = parts[0];
                    directory = 'images';
                }
                if (filename.indexOf('.') === -1) {
                    const ext = file.type.split('/')[1];
                    if (ext && ext !== 'octet-stream') {
                        filename += '.' + (ext === 'jpeg' ? 'jpg' : ext);
                    }
                }

                const targetFile = new File([file], filename, { type: file.type });
                const formData = new FormData();
                formData.append('file', targetFile);
                formData.append('directory', directory);

                const promise = apiRequest('/upload', { method: 'POST', body: formData, headers: {} })
                    .then(function (json) {
                        const url = json.url;
                        return {
                            ref: {
                                getDownloadURL: function () { return Promise.resolve(url); },
                            },
                        };
                    });
                return makeUploadTask(promise);
            },
        };
    }

    function kweekFileStore() {
        return kweekFileStore;
    }
    kweekFileStore.ref = function (path) { return makeFileRef(path || 'images'); };
    kweekFileStore.refFromURL = function (url) { return kweekFileStore.storage.refFromURL(url); };
    kweekFileStore.storage = {
        ref: function (path) { return kweekFileStore.ref(path); },
        refFromURL: function (url) {
            return {
                delete: function () {
                    return apiRequest('/delete-file', {
                        method: 'POST',
                        body: JSON.stringify({ url: url }),
                    });
                },
            };
        },
    };

    function KweekGeoQuery(db) {
        this._db = db;
    }
    KweekGeoQuery.prototype.collection = function (name) {
        const base = this._db.collection(name);
        return {
            near: function () { return base; },
            where: function (field, op, value) { return base.where(field, op, value); },
            get: function () { return base.get(); },
            onSnapshot: function (cb, err) { return base.onSnapshot(cb, err); },
            doc: function (id) { return base.doc(id); },
            add: function (data) { return base.add(data); },
        };
    };

    window.kweekDb = kweekDb;
    window.kweekFileStore = kweekFileStore;
    window.kweekGeoQuery = new KweekGeoQuery(kweekDbApi);
    window.KweekGeoQuery = KweekGeoQuery;
})();
