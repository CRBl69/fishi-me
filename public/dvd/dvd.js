// Declaring the HTML elements
const dvdElement = document.getElementById('dvd-image');
const timerElement = document.getElementById('timer');
const dvdContainerElement = document.getElementById('container');

// Declaring the mais variables
let x;
let y;
let addX;
let addY;
let timerDisplay = true;
let time = 0;
let calculating = false;
let dvdInterval = setInterval(dvd, 10);
let dvdTimerInterval = setInterval(dvdTimer, 10);

// Setting the initial position and directions
setPosDir();

// Keybinds
document.addEventListener('keypress', e => {
    if(e.key == 'r'){
        setPosDir();
        calculating = true;
        calc(x, y, addX, addY).then(res => {
            calculating = false;
            time = res;
        })
    }else if(e.key == 'h'){
        if(timerDisplay){
            timerElement.style.display = 'none';
        }else{
            timerElement.style.display = 'block';
        }
        timerDisplay = !timerDisplay;
    }
})

// Recalculating on window resizing
window.addEventListener('resize', res => {
    calculating = true;
    calc(x, y, addX, addY).then(res => {
        calculating = false;
        time = res;
    })
});

// The timer function
function dvdTimer(){
    if(calculating == true){
        timerElement.innerHTML = "calculating";
    }else if(time < 0){
        timerElement.style.fontSize = '6vw';
        if(time == -2){
            timerElement.innerHTML = "way too much";
        }else{
            calculating = true;
            calc(x, y, addX, addY).then(res => {
                calculating = false;
                time = res;
            });
        }
    }
    else{
        timerElement.style.fontSize = '20vw';
        timerElement.innerHTML = `${Math.floor(time / 1000)}S`;
        time -= 10;
    }
};

// Dvd main function
function dvd(){

    dvdElement.style.setProperty('top', x + 'px');
    dvdElement.style.setProperty('left', y + 'px');

    if(x >= getHeight()){
        addX = false;
    }else if (x <= 0){
        addX = true;
    }
    if(y >= getWidth()){
        addY = false;
    }else if (y <= 0){
        addY = true;
    }

    if(addX) x++;
    else x--;
    if(addY) y++;
    else y--;
}

// THE CALCULATOR
function calculate(X, Y, dirX, dirY){
    let time = 0;
    if(dirX && dirY){
        if(getHeight() - X < getWidth() - Y){
            time = (getHeight() - X) * 10;
            Y += getHeight() - X;
            X = getHeight();
        }else{
            time = (getWidth() - Y) * 10;
            X += getWidth() - Y;
            Y = getWidth();
        }
    }else if(!dirX && dirY){
        if(X < getWidth() - Y){
            time = X * 10;
            Y += X;
            X = 0;
        }else{
            time = (getWidth() - Y) * 10;
            X -= getWidth() - Y;
            Y = getWidth();
        }
    }else if(dirX && !dirY){
        if(getHeight() - X < Y){
            time = (getHeight() - X) * 10;
            Y -= getHeight() - X;
            X = getHeight();
        }else{
            time = Y * 10;
            X += Y;
            Y = 0;
        }
    }else{
        if(X < Y){
            time = X * 10;
            Y -= X;
            X = 0;
        }else{
            time = Y * 10;
            X -= Y;
            Y = 0;
        }
    }
    if((X == 0 && Y == 0) ||
        (X == 0 && Y == getWidth()) ||
        (X == getHeight() && Y == 0) ||
        (X == getHeight() && Y == getWidth())){
           return time;
    }else{
        if(X == 0) dirX = true;
        if(Y == 0) dirY = true;
        if(X == getHeight()) dirX = false;
        if(Y == getWidth()) dirY = false;
        let prevTime = calculate(X, Y, dirX, dirY);
        return prevTime != -2 ? prevTime + time : -2;
    }
}

// Async abstraction of THE CALCULATOR
async function calc(X, Y, dirX, dirY){
    let r = 0;
    try{
        r = calculate(X, Y, dirX, dirY);
    }catch{
        r = -2;
    }
    return r;
}

// Helper function to get the windows height
function getHeight(){
    dvdContainerStyle = window.getComputedStyle(dvdContainerElement);
    return parseInt(dvdContainerStyle.height.replace('px', '')) - dvdElement.height;
}

// Helper function to get the windows width
function getWidth(){
    dvdContainerStyle = window.getComputedStyle(dvdContainerElement);
    return parseInt(dvdContainerStyle.width.replace('px', '')) - dvdElement.width;
}

// Randomly sets the x, y, addX and adY variables
function setPosDir(){
    x = Math.floor(Math.random() * (getHeight()));
    y = Math.floor(Math.random() * (getWidth()));
    addX = Math.random() > .5 ? true : false;
    addY = Math.random() > .5 ? true : false;
}