function navigateToLink( $foldername , $user_id){
    console.log($user_id +" and "+$foldername);
    const url = `../Link/linkDisplay.php?user_id=${encodeURIComponent($user_id)}&foldername=${encodeURIComponent($foldername)}` 

    window.location.href = url;
}

// document.addEventListener('DOMContentLoaded',function(){
//     document.querySelectorAll('.folder-block').addEventListener('click',function(e){
//         var folderdata = e.target.getAttribute('data-foldername');
//         var userdata = e.target.getAttribute('data-username');
//         console.log(folderdata);
//         console.log(userdata);
//     })
// })