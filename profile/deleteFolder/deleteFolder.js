document.addEventListener('DOMContentLoaded',function(){

    const deleteButtons = document.querySelectorAll('.folder-button');
    const modal = document.getElementById('deleteFolderModal');
    const closeModal = modal.querySelector('.folder-close');
    const confirmDeleteBtn = modal.querySelector('.confirmdelete');

    let currentFolderName;
    let currentUID;


    deleteButtons.forEach(button =>{
        console.log(button);
        button.addEventListener('click',function(){
            const userID = this.getAttribute('data-userid');
            const folderName = this.getAttribute('data-foldername');
            console.log(userID , folderName);
            showModal(userID , folderName);
        })
    })


    function showModal(userID , folderName){
        currentUID = userID;
        currentFolderName = folderName;

       
        document.querySelector('.deletefoldername').textContent = folderName;

        modal.style.display = 'block';
    }

    closeModal.addEventListener('click' ,function(){
        modal.style.display = 'none';

    })

    confirmDeleteBtn.addEventListener('click' , function(){
        fetch('delete_folder.php' , {
            method:'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body:`userID=${currentUID}&folderName=${encodeURIComponent(currentFolderName)}`
        })
        .then(response =>{
            if(!response.ok){
                
                throw new Error('Network issue');
            }
            return response.json();
        })
        .then(data=>{
            if(data.success){
                alert('Folder Deleted Successfully');
                location.reload();
            }
            else{
                throw new Error('Error deleting folder');
            }
        })
        .catch(error=>{
            console.log(error);
            alert('An Error occured, try again letter');
        })
        modal.style.display = 'none';
    })
})