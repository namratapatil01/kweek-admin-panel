<?php

$file = 'e:/Nexa_Project/resources/views/settings/app/global.blade.php';
$content = file_get_contents($file);

// Replace file store calls
$helpers = <<<JS

        function mySqlKweekFileStore() {
            return {
                ref: function(path) {
                    return {
                        child: function(filename) {
                            return {
                                put: function(fileBlob) {
                                    var formData = new FormData();
                                    formData.append('file', fileBlob);
                                    formData.append('directory', path || 'images');

                                    var uploadTask = {
                                        on: function(event, progressCb, errorCb, completeCb) {
                                            if (event === 'state_changed') {
                                                if (progressCb) progressCb({ bytesTransferred: 50, totalBytes: 100 });
                                                
                                                fetch('{{ url("admin-data/upload") }}', {
                                                    method: 'POST',
                                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                                    body: formData
                                                }).then(r => r.json()).then(res => {
                                                    if (progressCb) progressCb({ bytesTransferred: 100, totalBytes: 100 });
                                                    uploadTask.snapshot = {
                                                        ref: {
                                                            getDownloadURL: function() { return Promise.resolve(res.url); }
                                                        }
                                                    };
                                                    if (completeCb) completeCb();
                                                }).catch(err => {
                                                    if (errorCb) errorCb(err);
                                                });
                                            }
                                        }
                                    };
                                    return uploadTask;
                                }
                            };
                        }
                    };
                }
            };
        }

        var storageRef = mySqlKweekFileStore().ref('images');
        var storageAudioRef = mySqlKweekFileStore().ref('audio');
        var storage = {
            refFromURL: function(url) {
                return {
                    bucket: "",
                    delete: function() {
                        return fetch('{{ url("admin-data/delete-file") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ url: url })
                        }).then(r => r.json());
                    }
                };
            }
        };
        // Override kweekFileStore to return mySqlKweekFileStore globally
        var kweekFileStore = function() { return mySqlKweekFileStore(); };
JS;

$content = str_replace("var storageRef = kweekFileStore().ref('images');", $helpers, $content);

file_put_contents($file, $content);
echo "Phase 2 complete";
