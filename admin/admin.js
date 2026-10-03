// admin panel js

// mobile sidebar
const menuBtn = document.getElementById("menuBtn");
const overlay = document.getElementById("overlay");

if (menuBtn) {
  menuBtn.addEventListener("click", function () {
    document.body.classList.toggle("menu-open");
  });
  overlay.addEventListener("click", function () {
    document.body.classList.remove("menu-open");
  });
}

// tabs on the content page
const tabs = document.querySelectorAll(".tab");
tabs.forEach(function (tab) {
  tab.addEventListener("click", function () {
    tabs.forEach(function (t) { t.classList.remove("on"); });
    document.querySelectorAll(".tab-panel").forEach(function (p) { p.classList.remove("on"); });

    tab.classList.add("on");
    document.getElementById(tab.dataset.tab).classList.add("on");
  });
});

// show picked image before uploading
document.querySelectorAll("input[type=file][data-preview]").forEach(function (input) {
  input.addEventListener("change", function () {
    const file = input.files[0];
    if (!file) return;

    const img = document.getElementById(input.dataset.preview);
    img.src = URL.createObjectURL(file);
    img.hidden = false;

    const noShot = document.getElementById("noShot");
    if (noShot) noShot.remove();
  });
});

// hide the green "saved" message after a few seconds
const okMsg = document.querySelector(".alert-ok");
if (okMsg) {
  setTimeout(function () {
    okMsg.style.transition = "opacity .4s";
    okMsg.style.opacity = "0";
    setTimeout(function () { okMsg.remove(); }, 400);
  }, 3500);
}
