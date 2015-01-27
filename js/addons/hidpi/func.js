(function(_, $) {

    if (Retina.isRetina()) {
        $(document).ready(function() {
            document.addEventListener("DOMNodeInserted", function (ev) {
                if (ev.target.tagName == 'IMG') { // pure js to speed up operation
                    new RetinaImage(ev.target, true);
                }
            }, false);
        });
    }
    
}(Tygh, Tygh.$));
