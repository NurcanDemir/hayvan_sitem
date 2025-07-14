document.addEventListener("DOMContentLoaded", function () {
  // Buton ve panel elementlerini seç
  const girisButonu = document.getElementById("girisButonu");
  const kayitButonu = document.getElementById("kayitButonu");
  const girisPaneli = document.getElementById("girisPaneli");
  const kayitPaneli = document.getElementById("kayitPaneli");
  const girisKapat = document.getElementById("girisKapat");
  const kayitKapat = document.getElementById("kayitKapat");

  // Giriş panelini aç
  if (girisButonu && girisPaneli) {
    girisButonu.addEventListener("click", function () {
      girisPaneli.classList.add("show");
      if (kayitPaneli) kayitPaneli.classList.remove("show");
    });
  }

  // Kayıt panelini aç
  if (kayitButonu && kayitPaneli) {
    kayitButonu.addEventListener("click", function () {
      kayitPaneli.classList.add("show");
      if (girisPaneli) girisPaneli.classList.remove("show");
    });
  }

  // Giriş panelini kapat
  if (girisKapat && girisPaneli) {
    girisKapat.addEventListener("click", function () {
      girisPaneli.classList.remove("show");
    });
  }

  // Kayıt panelini kapat
  if (kayitKapat && kayitPaneli) {
    kayitKapat.addEventListener("click", function () {
      kayitPaneli.classList.remove("show");
    });
  }
});
