function copyLink(e, link){
    const voidElement = document.createElement("input");
    voidElement.value = link;
    document.getElementsByTagName("body")[0].appendChild(voidElement);
    voidElement.select();
    document.execCommand("copy");
    document.getElementsByTagName("body")[0].removeChild(voidElement);
    e.classList.add("post-icon-click");
    setTimeout(() => {e.classList.remove("post-icon-click");}, 150);
}
function likePost(e){
    let link = e.id;
    e.classList.add("post-icon-click");
    setTimeout(() => {e.classList.remove("post-icon-click");}, 150);
    fetch(link).then(res => res.text()).then(res => {
        if(res == '1'){
            let like = e.parentNode.getElementsByTagName('span')[2];
            let likeNB = e.parentNode.getElementsByTagName('span')[1];
            if(like.innerHTML == 'favorite'){
                like.innerHTML = 'favorite_border';
                likeNB.innerHTML = parseInt(likeNB.innerHTML) - 1;
            }else{
                like.innerHTML = 'favorite';
                likeNB.innerHTML = parseInt(likeNB.innerHTML) + 1;
            }
        }
    })
}
function deletePost(e, postId){
    if(confirm("Do you really want to delete this post ?")){
        fetch('https://fishi.me/php/delete_post.php', {method: 'POST', body: JSON.stringify({postid: postId})}).then(res => res.text()).then(res => {
            if(res === '1'){
                e.parentNode.parentNode.parentNode.style.display  = 'none';
            }else{
                alert("Post could not be deleted, you are not an administrator/moderator");
            }
        });
    }
}

function openImage(e){
    let image = document.createElement('img');
    let imageContainer = document.createElement('div');
    let shadow = document.createElement('div');

    shadow.onclick = () => {
        document.getElementsByTagName('body')[0].removeChild(imageContainer);
        document.getElementsByTagName('body')[0].removeChild(shadow);
    }

    shadow.classList.add('shadow');
    image.setAttribute('src', e.target.src);
    imageContainer.classList.add('popup-image');
    document.getElementsByTagName('body')[0].insertBefore(shadow, document.getElementById('top-nav'));
    document.getElementsByTagName('body')[0].insertBefore(imageContainer, document.getElementById('top-nav'));
    imageContainer.append(image);
}