// 🌙 THEME TOGGLE
function toggleTheme(){
    const current = document.documentElement.getAttribute("data-theme");

    const next = current === "light" ? "dark" : "light";

    document.documentElement.setAttribute("data-theme", next);

    localStorage.setItem("theme", next);
}
function toggleNotif(){
    document.getElementById("notifDropdown")
        .classList.toggle("show");
}

/* fechar ao clicar fora */
document.addEventListener("click", function(e){
    const box = document.querySelector(".notif-wrapper");

    if(!box.contains(e.target)){
        document.getElementById("notifDropdown")
            .classList.remove("show");
    }
});
// load theme
const saved = localStorage.getItem("theme");
if(saved){
    document.documentElement.setAttribute("data-theme", saved);
}

// 📊 TABS
function openTab(tab, el){
    document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));

    document.getElementById(tab).classList.add('active');
    el.classList.add('active');
}
