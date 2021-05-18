
//Fonction des animation du trigger
function animateFrom(elem, direction) {
    direction = direction | 1;
    
    var x = 0,
        y = direction * 100;
    if(elem.classList.contains("gs_reveal_fromLeft")) { // class à mettre dans l'image à animer
    x = -1000;
    y = 0;
    } else if(elem.classList.contains("gs_reveal_fromRight")) {
    x = 1000;
    y = 0;
    } else if(elem.classList.contains("gs_reveal_fromTop")) {
    x = 0;
    y = -1200;
    } else if(elem.classList.contains("gs_reveal_fromBot")) {
    x = 0;
    y = 1200;
    }
    gsap.fromTo(elem, {x: x, y: y, autoAlpha: 0}, {
    duration: 1.25, 
    x: 0,
    y: 0, 
    autoAlpha: 1, 
    ease: "expo", 
    overwrite: "auto"
    });
}

//Fonction qui cache l'élément
function hide(elem) {
    gsap.set(elem, {autoAlpha: 0});
}



//Création du trigger
document.addEventListener("DOMContentLoaded", function() {
    //déclaration du trigger
    gsap.registerPlugin(ScrollTrigger);
    
    //Création de la class du trigger
    gsap.utils.toArray(".gs_reveal").forEach(function(elem) {
      hide(elem); // assure that the element is hidden when scrolled into view

    //Configuration du trigger
    ScrollTrigger.create({
        trigger: elem,
        markers: false,
        onEnter: function() { animateFrom(elem) }, 
        onEnterBack: function() { animateFrom(elem, -1) },
        onLeave: function() { hide(elem) } // assure that the element is hidden when scrolled into view
    });
    
    });

    //Création de la class du trigger
    gsap.utils.toArray(".animI").forEach(function(elem) {
        hide(elem); // assure that the element is hidden when scrolled into view

      //Configuration du trigger
        ScrollTrigger.create({
            trigger: elem,
            markers: true,
            onEnter: function() { animateFrom(elem) }, 
            onEnterBack: function() { animateFrom(elem, -1) },
            onLeave: function() { hide(elem) } // assure that the element is hidden when scrolled into view
        });

    });

});

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

