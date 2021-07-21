for(let canvas of document.getElementsByTagName("canvas")){
    let ctx = canvas.getContext('2d');
    let time = parseFloat(canvas.attributes.getNamedItem("timeleft").nodeValue);
    var grd = ctx.createRadialGradient(75, 50, 5, 90, 60, 100);
    grd.addColorStop(0, '#f9b004');
    grd.addColorStop(1, '#f94e04');
    ctx.strokeStyle = grd;
    ctx.beginPath();
    ctx.arc(35, 35, 34, 1.5*Math.PI, 1.5*Math.PI+2 * (time) * Math.PI);
    ctx.stroke();
    ctx.beginPath();
    ctx.arc(35, 35, 33, 1.5*Math.PI, 1.5*Math.PI+2 * (time) * Math.PI);
    ctx.stroke();
    ctx.beginPath();
    ctx.arc(35, 35, 32, 1.5*Math.PI, 1.5*Math.PI+2 * (time) * Math.PI);
    ctx.stroke();
}