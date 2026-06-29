(function () {
        const bridgeBase = document.querySelector('meta[name="kweek-data-bridge"]')?.getAttribute('content') || '/admin-data';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function bridgeRequest(path, options) {
            const headers = Object.assign({
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }, options.headers || {});
            if (!(options.body instanceof FormData)) {
                headers['Content-Type'] = 'application/json';
            }
            return fetch(bridgeBase + path, Object.assign({ credentials: 'same-origin', headers }, options))
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

        function makeSnapshot(id, data) {
            const exists = data != null;
            return {
                id: id,
                exists: exists,
                data: function () { return exists ? data : undefined; },
                get: function (field) { return exists ? data[field] : undefined; },
            };
        }

        function makeQuerySnapshot(docs) {
            const mapped = docs.map(function (row) {
                const id = row.id || row._id || '';
                return {
                    id: id,
                    exists: true,
                    data: function () { return row; },
                    get: function (field) { return row[field]; },
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
                    return bridgeRequest('/document/' + encodeURIComponent(collectionName) + '/' + encodeURIComponent(docId))
                        .then(function (json) { return makeSnapshot(docId, json.data || null); });
                },
                set: function (data) {
                    return bridgeRequest('/upsert', {
                        method: 'POST',
                        body: JSON.stringify({ collection: collectionName, id: docId, data: data, merge: false }),
                    });
                },
                update: function (data) {
                    return bridgeRequest('/upsert', {
                        method: 'POST',
                        body: JSON.stringify({ collection: collectionName, id: docId, data: data, merge: true }),
                    });
                },
                delete: function () {
                    return bridgeRequest('/document/' + encodeURIComponent(collectionName) + '/' + encodeURIComponent(docId), {
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
                    return bridgeRequest('/query', {
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
                    }).then(function (json) { return makeQuerySnapshot(json.data || []); });
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
                    return bridgeRequest('/upsert', {
                        method: 'POST',
                        body: JSON.stringify({ collection: collectionName, id: id, data: data, merge: false }),
                    }).then(function () { return makeDocRef(collectionName, id); });
                },
                onSnapshot: function (callback, errorCallback) {
                    return makeQuery(collectionName, [], {}).onSnapshot(callback, errorCallback);
                },
            };
        }

        const kweekFirestoreApi = {
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

        function kweekFirestore() {
            return kweekFirestoreApi;
        }
        kweekFirestore.FieldValue = kweekFirestoreApi.FieldValue;
        kweekFirestore.GeoPoint = kweekFirestoreApi.GeoPoint;
        kweekFirestore.Timestamp = kweekFirestoreApi.Timestamp;

        function makeStorageRef(path) {
            return {
                child: function (name) {
                    const childPath = (path ? path + '/' : '') + name;
                    return makeStorageRef(childPath);
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
                    const file = new File([blob], 'upload', { type: contentType });
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('directory', path || 'images');
                    return bridgeRequest('/upload', { method: 'POST', body: formData, headers: {} })
                        .then(function (json) {
                            const url = json.url;
                            return {
                                ref: {
                                    getDownloadURL: function () { return Promise.resolve(url); },
                                },
                            };
                        });
                },
                put: function (file) {
                        const formData = new FormData();
                        formData.append('file', file);
                        formData.append('directory', path || 'images');
                        return bridgeRequest('/upload', { method: 'POST', body: formData, headers: {} })
                            .then(function (json) {
                                const url = json.url;
                                return {
                                    ref: {
                                        getDownloadURL: function () { return Promise.resolve(url); },
                                    },
                                };
                            });
                    },
                };
        }
        const kweekStorage = {
            ref: function (path) { return makeStorageRef(path || 'images'); },
            refFromURL: function (url) { return kweekStorage.storage.refFromURL(url); },
            storage: {
                ref: function (path) { return kweekStorage.ref(path); },
                refFromURL: function (url) {
                    return {
                        delete: function () {
                            return bridgeRequest('/delete-file', {
                                method: 'POST',
                                body: JSON.stringify({ url: url }),
                            });
                        },
                    };
                },
            },
        };

        function GeoFirestore(db) {
            this._db = db;
        }
        GeoFirestore.prototype.collection = function (name) {
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

        window.kweekFirestore = kweekFirestore;
        window.kweekStorage = kweekStorage;
        window.kweekGeoFirestore = new GeoFirestore(kweekFirestoreApi);
        window.GeoFirestore = GeoFirestore;
    })();
