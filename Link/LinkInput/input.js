document.addEventListener('DOMContentLoaded', function(){

    document.getElementById('linkInputForm').addEventListener('submit',function(e){
        e.preventDefault();
        const formData = new FormData(this);

        for(let[key,value] of formData.entries()){
            console.log(key +"--"+ value);
        }

        fetch('input.php',{
            method:'post',
            body:formData
        })
        .then((response)=>{
            if(!response.ok){
                throw new Error('Network issue');
            }
            else return response.json();
        })
        .then((data)=>{
            if(data.success){
                alert('profile updated ');
                window.location.reload();
            }
            else{
                alert('some error while inserting the link'+data.msg);
            }
        })
        .catch((err)=>{
            console.log(err);
            alert('some error occured'+err);
        })
    })
})