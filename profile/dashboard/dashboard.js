document.addEventListener('DOMContentLoaded', function(){
    
    const modal = document.getElementById('editModal');
    const editButton = document.querySelector('.edit_button');
    const closeButton = document.querySelector('.close');
    
    editButton.addEventListener('click', () => modal.style.display = "block");
        
        closeButton.addEventListener('click', () => modal.style.display = "none");
        
        window.addEventListener('click', (event) => {
            if (event.target === modal) modal.style.display = "none";
        });

        document.getElementById('editForm').addEventListener('submit',function(event){
            event.preventDefault();

            const formData = new FormData(this);
            
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            fetch('edit_profile.php',{
                method:'POST',
                body:formData,
            })
            .then((response=>{
                if(!response.ok){
                    throw new Error('Network issue');
                }

                return response.json();
            }))

            .then(data=>{
                if(data.success){
                    alert('profile updated ');

                    if(data.usernameChanged){
                        window.location.href = data.redirectUrl;
                    }
                    else{
                        window.location.reload();
                    }
                }
                else{
                    alert('failed to update profile'+data.message);
                }
            })
            .catch((err)=>{
                console.log(err);
                alert('some error occured, tru again letter', +err)
            })
        })
})
