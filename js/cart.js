/**
 * cart.js — dimuat sebelum main.js
 */
(function () {
  "use strict";

  function getCsrf() {
    if (window.__CSRF_TOKEN) return String(window.__CSRF_TOKEN);
    var el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute("content") || "" : "";
  }

  function cartUrl() {
    // Selalu relatif ke folder yang sama dengan halaman
    try {
      return new URL("cart_action.php", window.location.href).href;
    } catch (e) {
      return "cart_action.php";
    }
  }

  function ensureToast() {
    var t = document.getElementById("toastCart");
    if (t) return t;
    t = document.createElement("div");
    t.id = "toastCart";
    t.className = "toast-cart";
    t.innerHTML = '<i class="fas fa-check-circle"></i><span id="toastCartMsg">Ditambahkan</span>';
    document.body.appendChild(t);
    return t;
  }

  function showToast(msg) {
    var t = ensureToast();
    var msgEl = document.getElementById("toastCartMsg");
    if (msgEl) msgEl.textContent = msg;
    t.classList.add("show");
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(function () {
      t.classList.remove("show");
    }, 2500);
  }

  function updateBadge(count) {
    count = parseInt(count, 10) || 0;
    var b = document.getElementById("navCartBadge");
    if (b) {
      b.textContent = String(count);
      b.style.display = count > 0 ? "flex" : "none";
    }
  }

  window.addToCart = function (produkId, qty) {
    qty = qty || 1;
    produkId = parseInt(produkId, 10);
    if (!produkId) {
      alert("Produk tidak valid");
      return Promise.resolve({ success: false });
    }

    var fd = new FormData();
    fd.append("action", "add");
    fd.append("produk_id", String(produkId));
    fd.append("qty", String(qty));
    fd.append("csrf_token", getCsrf());

    var url = cartUrl();
    console.log("[cart] POST", url, "id=", produkId);

    return fetch(url, {
      method: "POST",
      body: fd,
      credentials: "same-origin",
      headers: { "X-Requested-With": "XMLHttpRequest" }
    })
      .then(function (r) {
        return r.text().then(function (text) {
          var trimmed = (text || "").replace(/^\uFEFF/, "").trim();
          if (!trimmed) {
            throw new Error("Server mengembalikan respons kosong (HTTP " + r.status + ")");
          }
          try {
            return JSON.parse(trimmed);
          } catch (e) {
            console.error("[cart] raw response:", trimmed.substring(0, 500));
            throw new Error("Respons bukan JSON. Cuplikan: " + trimmed.substring(0, 120));
          }
        });
      })
      .then(function (res) {
        if (res.success) {
          updateBadge(res.cart_count);
          showToast(res.message || "Ditambahkan ke keranjang");
        } else {
          alert(res.message || "Gagal menambahkan ke keranjang");
        }
        return res;
      })
      .catch(function (err) {
        console.error("[cart] error", err);
        alert("Gagal menambah ke keranjang.\n\n" + (err.message || err));
        return { success: false };
      });
  };

  window.addToCartClick = function (produkId, btn) {
    produkId = parseInt(produkId, 10);
    if (!produkId) {
      alert("ID produk tidak ditemukan");
      return false;
    }
    window.addToCart(produkId, 1).then(function (res) {
      if (res && res.success && btn) {
        var ico = btn.querySelector("i");
        if (ico) ico.className = "fas fa-check";
        btn.classList.add("added");
        setTimeout(function () {
          if (ico) ico.className = "fas fa-plus";
          btn.classList.remove("added");
        }, 1000);
      }
    });
    return false;
  };

  window.updateCartBadge = updateBadge;
  console.log("[cart] ready");
})();
