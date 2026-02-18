document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("valet-ajax-search");
    if (searchInput) {
        let debounceTimer;

        searchInput.addEventListener("keyup", function () {
            let query = searchInput.value.trim();

            // Only trigger search if at least 2 characters are entered
            if (query.length < 2 && query.length != 0) {
                return;
            }

            // Clear the previous debounce timer
            clearTimeout(debounceTimer);

            // Set a new debounce timer (1 second delay)
            debounceTimer = setTimeout(() => {
                searchChangePageAjaxCall(null, query)
            }, 400);
        });
    }
});

function changePage(e, page) {
    e.preventDefault();
    const params = new URLSearchParams(window.location.search);
    const search = params.get("search") || '';
    searchChangePageAjaxCall (page, search)
}

function searchChangePageAjaxCall (page, search) {
    const loader = document.getElementById('contact-table-loader');
    const tableBody = document.getElementById("contact-table-body");
    const pagination =  document.getElementById('contact-table-pagination');
    let formData = new FormData();
    formData.append("action", "search_contacts");
    formData.append("search", search);
    formData.append("page", page);
    formData.append("nonce", ajax_search_params.nonce); // Security nonce

    loader.style.display = 'flex';
    tableBody.style.display = 'none';
    pagination.style.display = 'none';

    fetch(ajax_search_params.ajax_url, {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json()) // Parse response as JSON
        .then((data) => {
            // Check if the response is successful and has the 'html' key
            if (data.success && data.data.html) {
                // Update the contact table with the returned HTML
                tableBody.innerHTML = data.data.html;
            } else {
                console.error("Error: No contacts found or invalid response.");
            }
            loader.style.display = 'none';
            tableBody.style.display = 'flex';
            if(data.success && data.data.pagination) {
                pagination.style.display = 'block';
                pagination.innerHTML = data.data.pagination;
            } else {
                if (pagination.style.display != 'none') {
                    pagination.style.display = 'none';
                }
            }
            updateAjaxURL(data.data.search, data.data.page)
        })
        .catch((error) => console.error("Error:", error));
}

function updateAjaxURL(search, page) {
    const params = new URLSearchParams(window.location.search);


    if (search) {
        params.set("search", search);
    } else {
        params.delete("search");
    }
    console.log(page);
    // Update or remove page param
    if (page && page != "1") {
        params.set("pg", page);
    } else {
        params.delete("pg");
    }

    // Push new state to history without reloading
    const newUrl = window.location.pathname + (params.toString() ? "?" + params.toString() : "");
    window.history.pushState({}, "", newUrl);
}