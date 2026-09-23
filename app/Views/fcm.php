<!-- https://flaviocopes.com/firebase-firestore/ -->
<!--
    FCM RULE
    rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /{document=**} {
      allow read, write;
    }
  }
} -->

<script src="https://www.gstatic.com/
firebasejs/7.2.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/
firebasejs/7.2.1/firebase-firestore.js"></script>

<script>
document.addEventListener('DOMContentLoaded', event => {


});
const firebaseConfig = {
    apiKey: "AIzaSyDC9hQGSTybAK9t3e6sD6BautgFtTNgQv0",
  authDomain: "unitglodemo-b57e3.firebaseapp.com",
  databaseURL: "https://unitglodemo-b57e3.firebaseio.com",
  projectId: "unitglodemo-b57e3",
  storageBucket: "unitglodemo-b57e3.appspot.com",
  messagingSenderId: "724618605406",
  appId: "1:724618605406:web:81f1451cbd1bba4e4fd5cf"
}

firebase.initializeApp(firebaseConfig);
const db = firebase.firestore();
/*
const list = [];

list.forEach(item => {
  db.collection('brandm').doc(item).set({})
});*/
const list = <?=json_encode($device_list)?>

list.forEach(item => {
  db.collection('unitglo-ads').doc(item).set({"device_id":true})
  console.log(item);
})

// const docRef = db.doc(`/unitglo-ads/cust_id_user_id`);
// docRef.get().then(function(doc) {
//   if (doc.exists) {
//     const data = doc.data();
//     console.log(data);
//     data.device_id=true;
//     docRef.update(data)

//     // document.querySelector('button')
//     //   .addEventListener('click', () => {
//     //   data[course] = true
//     //   docRef.update(data)
//     // })
//     } else {
//     //user does not exist..
//   }
// });
// console.log(docRef);

</script>
