document.addEventListener('DOMContentLoaded', () => {
    const sourceSelect = document.getElementById('source-select');
    const fetchButton = document.getElementById('fetch-button');
    const newsContainer = document.getElementById('news-container');
    const loadingIndicator = document.getElementById('loading-indicator');
    const errorMessageElement = document.getElementById('error-message');

    const paginationControls = document.getElementById('pagination-controls');
    const prevPageButton = document.getElementById('prev-page');
    const nextPageButton = document.getElementById('next-page');
    const currentPageSpan = document.getElementById('current-page');
    const totalPagesSpan = document.getElementById('total-pages');

    const ITEMS_PER_PAGE = 5; // Number of news articles per page
    let allArticles = [];
    let currentPage = 1;

    fetchButton.addEventListener('click', fetchNews);

    async function fetchNews() {
        const selectedSource = sourceSelect.value;
        if (!selectedSource) {
            showError('Please select a source.');
            return;
        }

        showLoading(true);
        clearNews();
        hideError();
        hidePagination();

        try {
            const response = await fetch(`api.php?source=${selectedSource}`);
            
            // Get response text to handle both JSON and HTML responses
            const responseText = await response.text();
            
            // Check if the response is HTML (likely an error page)
            if (responseText.trim().startsWith('<')) {
                console.error('Server returned HTML instead of JSON:', responseText);
                throw new Error('Server returned an error. Check the parser configuration and website availability.');
            }
            
            // Try to parse the response as JSON
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (jsonError) {
                console.error('Failed to parse JSON:', responseText);
                throw new Error('Failed to parse server response. Check server logs.');
            }

            if (!response.ok) {
                throw new Error(data.error || `HTTP Error: ${response.status}`);
            }

            if (data.error) {
                throw new Error(data.error);
            }
            
            allArticles = data.articles || [];
            if (allArticles.length === 0) {
                showError('No news found for this source. The selectors may need to be updated.');
            } else {
                currentPage = 1;
                renderPage();
                showPagination();
            }

        } catch (error) {
            console.error('Error while fetching news:', error);
            showError(`Failed to load news: ${error.message}`);
            allArticles = []; // Clear articles array on error
        } finally {
            showLoading(false);
        }
    }

    function renderPage() {
        clearNews();
        const totalItems = allArticles.length;
        const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);

        if (totalItems === 0) {
            hidePagination();
            return;
        }
        
        currentPageSpan.textContent = currentPage;
        totalPagesSpan.textContent = totalPages;

        const start = (currentPage - 1) * ITEMS_PER_PAGE;
        const end = start + ITEMS_PER_PAGE;
        const articlesToShow = allArticles.slice(start, end);

        articlesToShow.forEach(article => {
            const newsItemDiv = document.createElement('div');
            newsItemDiv.classList.add('news-item');

            let imageHtml = '';
            if (article.imageUrl) {
                // Validate that the image URL is correct
                try {
                    new URL(article.imageUrl);
                    imageHtml = `<img src="${escapeHtml(article.imageUrl)}" alt="${escapeHtml(article.title)}" onerror="this.style.display='none'">`;
                } catch (e) {
                    console.warn(`Invalid image URL: ${article.imageUrl}`);
                }
            }

            newsItemDiv.innerHTML = `
                ${imageHtml}
                <div class="news-item-content">
                    <h2><a href="${escapeHtml(article.sourceUrl)}" target="_blank" rel="noopener noreferrer">${escapeHtml(article.title)}</a></h2>
                    <p>${escapeHtml(article.description || 'No description available.')}</p>
                </div>
            `;
            newsContainer.appendChild(newsItemDiv);
        });

        prevPageButton.disabled = currentPage === 1;
        nextPageButton.disabled = currentPage === totalPages || totalPages === 0;
        showPagination();
    }

    function clearNews() {
        newsContainer.innerHTML = '';
    }

    function showLoading(isLoading) {
        loadingIndicator.style.display = isLoading ? 'block' : 'none';
    }

    function showError(message) {
        errorMessageElement.textContent = message;
        errorMessageElement.style.display = 'block';
    }

    function hideError() {
        errorMessageElement.style.display = 'none';
    }
    
    function showPagination() {
        if (allArticles.length > 0) {
            paginationControls.style.display = 'block';
        } else {
            paginationControls.style.display = 'none';
        }
    }

    function hidePagination() {
        paginationControls.style.display = 'none';
    }

    prevPageButton.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderPage();
        }
    });

    nextPageButton.addEventListener('click', () => {
        const totalPages = Math.ceil(allArticles.length / ITEMS_PER_PAGE);
        if (currentPage < totalPages) {
            currentPage++;
            renderPage();
        }
    });

    // Function to escape HTML (simple version)
    function escapeHtml(unsafe) {
        if (unsafe === null || typeof unsafe === 'undefined') {
            return '';
        }
        return unsafe
             .toString()
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }
});
