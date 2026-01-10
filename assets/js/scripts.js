function toggleProfile(){
    document.getElementById('profileMenu').classList.toggle('active');
}

function toggleDarkMode(e){
    e.stopPropagation();
    document.body.classList.toggle('dark');
    document.getElementById('darkToggle').classList.toggle('active');
}

document.addEventListener('click',function(e){
    if(!e.target.closest('.profile-wrapper')){
        document.getElementById('profileMenu').classList.remove('active');
    }
});