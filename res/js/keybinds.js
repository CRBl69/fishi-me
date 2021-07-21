document.addEventListener('keydown', (e) => {
    if(e.key == 'Escape'){
        let inputs = document.getElementsByTagName('input');
        for(const input of inputs){
            if(input === document.activeElement) input.blur();
        }
        let textareas = document.getElementsByTagName('textarea');
        for(const input of textareas){
            if(input === document.activeElement) input.blur();
        }
    }
})

document.addEventListener("keypress", (e) => {
    if(location.href.match(/^https:\/\/fishi\.me\/dm\//)){
        if (e.key === 'Enter') {
            if(location.href !== 'https://fishi.me/dm/' && chatInput === document.activeElement){
                sendMsg(chatInput.value);
                chatInput.value = '';
            }
            return;
        }
    }
    let inputs = document.getElementsByTagName('input');
    let inputIsFocused = false;
    for(const input of inputs){
        if(input === document.activeElement) inputIsFocused = true;
    }
    let textareas = document.getElementsByTagName('textarea');
    for(const input of textareas){
        if(input === document.activeElement) inputIsFocused = true;
    }
    if(!inputIsFocused){
        e.preventDefault();
        if(location.href.match(/^https:\/\/fishi\.me\/dm\//)){
            if(e.key === 't'){
                chatInput.focus()
                return;
            }
            if(e.key === 'c'){
                document.getElementById('search-dm').focus()
                return;
            }
        }
        if(location.href.match(/^https:\/\/fishi\.me\/profile/)){
            if(e.key == 'd'){
                location.href = document.querySelector('.dm-button').href;
                return
            }
        }
        if(location.href.match(/^https:\/\/fishi\.me\/home/)){
            if(e.key == 'n'){
                let pgNb = 1;
                if(location.search.match('page')){
                    pgNb = parseInt(location.search.match(/page=(\d)+/)[1]);
                }
                location.href = '/home?page=' + (pgNb + 1);
                return;
            }
            if(e.key == 'b'){
                let pgNb = 1;
                if(location.search.match('page')){
                    pgNb = parseInt(location.search.match(/page=(\d)+/)[1]);
                }
                location.href = '/home?page=' + (pgNb - 1 > 0 ? pgNb - 1 : 1);
                return;
            }
        }
        if(location.href.match(/^https:\/\/fishi\.me\/likes/)){
            if(e.key == 'n'){
                let pgNb = 1;
                if(location.search.match('page')){
                    pgNb = parseInt(location.search.match(/page=(\d)+/)[1]);
                }
                location.href = '/likes?page=' + (pgNb + 1);
                return;
            }
            if(e.key == 'b'){
                let pgNb = 1;
                if(location.search.match('page')){
                    pgNb = parseInt(location.search.match(/page=(\d)+/)[1]);
                }
                location.href = '/likes?page=' + (pgNb - 1 > 0 ? pgNb - 1 : 1);
                return;
            }
        }
        if(e.key == 'd'){
            location.href = '/dm';
            return;
        }
        if(e.key == 'p'){
            location.href = '/profile';
            return;
        }
        if(e.key == 's'){
            searchInput.focus()
            return;
        }
        if(e.key == 'l'){
            location.href = '/likes';
            return;
        }
        if(e.key == 'h'){
            location.href = '/home';
            return;
        }
    }
})
