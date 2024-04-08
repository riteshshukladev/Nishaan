document.addEventListener('DOMContentLoaded', function() {
    


    document.querySelector('#addFolderForm').addEventListener('submit', function(event){

        event.preventDefault();

        const formData = new FormData(this);

        for(let [key,values] of formData.entries()){
            console.log(key,values);
        }
        fetch('add_floder.php',{
            method:'POST',
            body:formData,
        })
        .then((response=>{
            if(!response.ok){
                throw new Error('Network Issue! Folder Creation Failed');
            }

                console.log(response);
                return response.json();
        }))
        .then(data=>{
            if(data.success){
                alert('profile updated');
                window.location.reload();
            }
            else{
                alert('failed to add folder',+data.message);
            }
        })
        // folderButton.style.display = 'none';
        .catch((err)=>{
            console.log(err);
            alert("some error occured, try again letter", +err);
        })
    })
});
