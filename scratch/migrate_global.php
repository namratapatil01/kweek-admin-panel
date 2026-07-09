<?php

$file = 'e:/Nexa_Project/resources/views/settings/app/global.blade.php';
$content = file_get_contents($file);

// Add the Migration helper functions
$helpers = <<<JS
        var kweekDbMigrated = {
            updateDoc: function(docId, data) {
                return fetch('{{ url("admin-data/upsert") }}', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    body: JSON.stringify({collection: 'settings', id: String(docId), data: data, merge: true})
                }).then(r => r.json());
            },
            setDoc: function(docId, data) {
                return fetch('{{ url("admin-data/upsert") }}', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    body: JSON.stringify({collection: 'settings', id: String(docId), data: data, merge: false})
                }).then(r => r.json());
            },
            getDoc: function(docId) {
                return fetch('{{ url("admin-data/document/settings") }}/' + docId)
                    .then(r => r.json())
                    .then(res => {
                        if(res && res.data) {
                            return { exists: true, data: function() { return res.data; } };
                        }
                        return { exists: false, data: function() { return null; } };
                    }).catch(e => { return { exists: false, data: function() { return null; } }; });
            }
        };

        async function uploadFileToMysql(fileBlob) {
            var formData = new FormData();
            formData.append('file', fileBlob);
            formData.append('directory', 'images');
            return fetch('{{ url("admin-data/upload") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            }).then(r => r.json()).then(res => res.url);
        }

        var database = kweekDb();
JS;

$content = str_replace('var database = kweekDb();', $helpers, $content);

// Replace document operations
$content = preg_replace("/database\.collection\('settings'\)\.doc\((['\"])([a-zA-Z0-9_]+)\\1\)\.get\(\)/", 'kweekDbMigrated.getDoc("$2")', $content);
$content = preg_replace("/database\.collection\('settings'\)\.doc\((['\"])([a-zA-Z0-9_]+)\\1\)\.update\(/", 'kweekDbMigrated.updateDoc("$2", ', $content);
$content = preg_replace("/database\.collection\('settings'\)\.doc\((['\"])([a-zA-Z0-9_]+)\\1\)\.set\(/", 'kweekDbMigrated.setDoc("$2", ', $content);

// Replace currency logic (which uses a 'where' query on 'currencies' collection)
// Actually currencies in global settings is only for default country dropdown.
// Let's replace `refCurrency.get().then` with a hardcoded promise or fetch
$content = str_replace(
    'var refCurrency = database.collection(\'currencies\').where(\'isActive\', \'==\', true);', 
    'var refCurrency = { get: function() { return fetch(\'{{ url("admin-data/query") }}\', { method: \'POST\', headers: {\'Content-Type\': \'application/json\', \'X-CSRF-TOKEN\': \'{{ csrf_token() }}\'}, body: JSON.stringify({collection: \'currencies\', filters: [[\'isActive\', \'==\', true]]}) }).then(r => r.json()).then(res => { var docs = (res.data||[]).map(d => ({id: d.id, data: function(){return d;}})); return { docs: docs, forEach: function(cb){ docs.forEach(cb); } }; }); } };',
    $content
);

// We must also handle "ref.get()", "mapKey.get()", "refPlaceholderImage.get()"
$content = preg_replace("/var ([a-zA-Z0-9_]+) = database\.collection\('settings'\)\.doc\((['\"])([a-zA-Z0-9_]+)\\2\);/", 'var $1 = { get: function() { return kweekDbMigrated.getDoc("$3"); }, update: function(data) { return kweekDbMigrated.updateDoc("$3", data); }, set: function(data) { return kweekDbMigrated.setDoc("$3", data); } };', $content);

file_put_contents($file, $content);
echo "Phase 1 complete";
