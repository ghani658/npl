// document.addEventListener("DOMContentLoaded", function () {
//     const tabLinks = document.querySelectorAll(".tab-links a");
//     const tabs = document.querySelectorAll(".tab-content .tab");

//     tabLinks.forEach(link => {
//         link.addEventListener("click", function (e) {
//             e.preventDefault();

//             // Remove active class from all tabs and links
//             tabLinks.forEach(link => link.parentNode.classList.remove("active"));
//             tabs.forEach(tab => tab.classList.remove("active"));

//             // Add active class to clicked tab and link
//             this.parentNode.classList.add("active");
//             const target = document.querySelector(this.getAttribute("href"));
//             target.classList.add("active");
//         });
//     });
// });


const tabs = document.querySelectorAll(".tabs");
const tab = document.querySelectorAll(".tab");
const panel = document.querySelectorAll(".panel");

function onTabClick(event) {

  // deactivate existing active tabs and panel

  for (let i = 0; i < tab.length; i++) {
    tab[i].classList.remove("active");
  }

  for (let i = 0; i < panel.length; i++) {
    panel[i].classList.remove("active");
  }


  // activate new tabs and panel
  event.target.classList.add('active');
  let classString = event.target.getAttribute('data-target');
  console.log(classString);
  document.getElementById('panels').getElementsByClassName(classString)[0].classList.add("active");
}

for (let i = 0; i < tab.length; i++) {
  tab[i].addEventListener('click', onTabClick, false);
}



document.addEventListener("DOMContentLoaded", function() {
    // Select the 2nd, 3rd, and 4th .post-item elements
    var postItems = document.querySelectorAll('.pre_league_news_blog .post-item:nth-child(2), .pre_league_news_blog .post-item:nth-child(3), .pre_league_news_blog .post-item:nth-child(4)');
    
    postItems.forEach(function(postItem) {
        var wrapper = document.createElement('div');
        wrapper.classList.add('your-wrapper-class');  
        postItem.parentNode.insertBefore(wrapper, postItem);  
        wrapper.appendChild(postItem); 
    });
});