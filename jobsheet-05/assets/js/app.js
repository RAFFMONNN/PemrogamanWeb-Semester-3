// ===== Hamburger menu (JS-driven, menggantikan checkbox hack) =====
function initNavToggle() {
    const toggleBtn = document.getElementById("nav-toggle-btn");
    const nav = document.querySelector("header nav");
    if (!toggleBtn || !nav) return;

    toggleBtn.addEventListener("click", function () {
        nav.classList.toggle("nav-open");
    });
}

// ===== Konfirmasi hapus (front-end only, belum ke server) =====
function initHapusConfirm() {
    document.querySelectorAll(".btn-hapus").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const row = btn.closest("tr");
            const nama = row ? row.querySelector("td")?.textContent : "data ini";
            const yakin = confirm("Yakin ingin menghapus \"" + nama + "\"?");
            if (yakin && row) {
                row.remove();
                updateRowCounter();
            }
        });
    });
}

// ===== Filter/pencarian tabel real-time =====
function initTableFilter() {
    const input = document.getElementById("search-input");
    const table = document.querySelector(".table-responsive table");
    if (!input || !table) return;

    input.addEventListener("keyup", function () {
        const keyword = input.value.toLowerCase();
        const rows = table.querySelectorAll("tbody tr");
        rows.forEach(function (row) {
            const teks = row.querySelector("td")?.textContent.toLowerCase() || ""; 
            // perluas initTableFilter pada kode diatas
            row.style.display = teks.includes(keyword) ? "" : "none";
        });
        updateRowCounter();
    });
}

// ===== Validasi form (client-side) =====
function tampilkanError(input, pesan) {
    hapusError(input);
    const span = document.createElement("span");
    span.className = "error";
    span.textContent = pesan;
    input.insertAdjacentElement("afterend", span);
}

function hapusError(input) {
    const next = input.nextElementSibling;
    if (next && next.classList.contains("error")) {
        next.remove();
    }
}

function initValidasiForm() {
    const form = document.getElementById("form-tambah");
    if (!form) return;

    form.addEventListener("submit", function (e) {
        let valid = true;

        const fieldWajib = [
            { selector: "[name='judul'], [name='nama']", message: "Field ini wajib diisi." },
            { selector: "[name='pengarang']", message: "Pengarang wajib diisi." },
        ];
        fieldWajib.forEach(function (item) {
            const input = form.querySelector(item.selector);
            if (input && input.value.trim() === "") {
                tampilkanError(input, item.message);
                valid = false;
            } else if (input) {
                hapusError(input);
            }
        });    

        const tahun = form.querySelector("[name='tahun']");
        if (tahun) {
            const nilai = parseInt(tahun.value, 10);
            if (isNaN(nilai) || nilai < 1900 || nilai > 2026) {
                tampilkanError(tahun, "Tahun harus di antara 1900-2026.");
                valid = false;
            } else {
                hapusError(tahun);
            }
        }

        const stok = form.querySelector("[name='stok']");
        if (stok) {
            const nilai = parseInt(stok.value, 10);
            if (isNaN(nilai) || nilai < 0) {
                tampilkanError(stok, "Stok tidak boleh negatif.");
                valid = false;
            } else {
                hapusError(stok);
            }
        }

        const isbn = form.querySelector("[name='isbn']");
        if (isbn && isbn.value.trim() === "") {
            const isbnPattern = /^[0-9\-]+$/; 
            if (!isbnPattern.test(isbn.value)) {
                tampilkanError(isbn, "Format ISBN tidak valid.");
                valid = false;
            } else {
                hapusError(isbn);
            }
        } else if (isbn) {
            hapusError(isbn);
        }

        if (!valid) {
            e.preventDefault();
        }
    });
}

function updateRowCounter() {
    const table = document.querySelector(".table-responsive table");
    const counter = document.getElementById("row-counter");
    if (!table || !counter) return;

    const rows = table.querySelectorAll("tbody tr");
    const totalRows = rows.length;

    let visibleRows = 0;
    rows.forEach(function (row) {
        if (row.style.display !== "none") {
            visibleRows++;
        }
    });
    counter.textContent = `Menampilkan ${visibleRows} dari ${totalRows} baris.`;
}   

document.addEventListener("DOMContentLoaded", function () {
    initNavToggle();
    initHapusConfirm();
    initTableFilter();
    initValidasiForm();
    updateRowCounter(); // Update counter on page load
});
