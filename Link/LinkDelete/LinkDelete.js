const delbutton = document.addEventListener('DOMContentLoaded', function(){
    const delbutton = document.querySelectorAll('.delete-link')


    delbutton.forEach(element => {
        element.addEventListener('click', function(){
            const userId = this.getAttribute('data-userid');
            const foldername = this.getAttribute('data-foldername');
            const linkno = this.getAttribute('data-linknum');


            console.log(userId +""+ foldername +""+linkno);

            fetch('link_delete.php' , {
                method:'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body:`userID=${userId}&folderName=${encodeURIComponent(foldername)}&linknum=${encodeURIComponent(linkno)}`
            })
            .then(response =>{
                if(!response.ok){
                    
                    throw new Error('Network issue');
                }
                return response.json();
            })
            .then(data=>{
                if(data.success){
                    alert('Link Deleted Successfully');
                    location.reload();
                }
                else{
                    throw new Error('Error deleting Link');
                }
            })
            .catch(error=>{
                console.log(error);
                alert('An Error occured, try again letter');
            })
        })
    });

})