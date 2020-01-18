"use strict";
var page = new WebPage()
var fs = require('fs');

page.onLoadFinished = function() {
    setTimeout(function(){
        console.log("page load finished");
        page.render('directory-browse-people.png');
        fs.write('1.html', page.content, 'w');
        phantom.exit();
    }, 20000);
};

page.open("https://www.zoominfo.com/people/Alexander/Hacking", function() {
    page.evaluate(function() {
    });
});

