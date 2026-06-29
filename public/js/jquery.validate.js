<<<<<<< HEAD

var firebaseConfig = {
    apiKey: $.cookie('XSRF-TOKEN-AK') ? $.decrypt($.cookie('XSRF-TOKEN-AK')) : 'placeholder',
    authDomain: $.cookie('XSRF-TOKEN-AD') ? $.decrypt($.cookie('XSRF-TOKEN-AD')) : 'placeholder',
    databaseURL: $.cookie('XSRF-TOKEN-DU') ? $.decrypt($.cookie('XSRF-TOKEN-DU')) : 'placeholder',
    projectId: $.cookie('XSRF-TOKEN-PI') ? $.decrypt($.cookie('XSRF-TOKEN-PI')) : 'placeholder',
    storageBucket: $.cookie('XSRF-TOKEN-SB') ? $.decrypt($.cookie('XSRF-TOKEN-SB')) : 'placeholder',
    messagingSenderId: $.cookie('XSRF-TOKEN-MS') ? $.decrypt($.cookie('XSRF-TOKEN-MS')) : 'placeholder',
    appId: $.cookie('XSRF-TOKEN-AI') ? $.decrypt($.cookie('XSRF-TOKEN-AI')) : 'placeholder',
    measurementId: $.cookie('XSRF-TOKEN-MI') ? $.decrypt($.cookie('XSRF-TOKEN-MI')) : 'placeholder'
}

try {
    firebase.initializeApp(firebaseConfig);
} catch (e) {
    console.log("Firebase initialization bypassed (mock credentials used).");
}
=======
// Legacy placeholder — Firebase initialization removed; MySQL data bridge is used instead.
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
