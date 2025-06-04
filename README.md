# News Parser Application

A containerized PHP web application that aggregates and displays news articles from multiple sources including RT.com, Vesti.kg, and Azattyk (Kyrgyz service).

## Features

- **Multi-source news aggregation**: Fetches news from RT.com (English), Vesti.kg (Russian), and Azattyk (Russian)
- **Web-based interface**: Clean, responsive UI with pagination
- **REST API**: JSON API endpoint for programmatic access
- **Docker containerized**: Easy deployment with Docker Compose
- **Real-time parsing**: Dynamically extracts news articles using CSS selectors
- **Image support**: Handles lazy-loaded images and various image formats
- **Error handling**: Robust error handling with detailed logging

## Architecture

- **Backend**: PHP 8.1 with Guzzle HTTP client and Symfony DomCrawler
- **Frontend**: Vanilla JavaScript with responsive CSS
- **Web Server**: Nginx as reverse proxy
- **Containerization**: Docker with PHP-FPM and Nginx containers

## Prerequisites

- Docker (version 20.10 or higher)
- Docker Compose (version 2.0 or higher)
- Git (for cloning the repository)

## Quick Start

### 1. Clone the Repository

```bash
git clone https://github.com/ErlanKurmanov/news-parser.git
cd news-parser
```

### 2. Start the Application

```bash
docker-compose up -d --build
```

```bash
docker-compose exec app composer install
```

the application will be available at http://localhost:8080

### 3. Access the Application

Open your web browser and navigate to:
```
http://localhost:8080
```

## Usage

### Web Interface

1. Select a news source from the dropdown menu:
    - **Vesti.kg**: Kyrgyz news in Russian
    - **Azattyk**: Radio Free Europe/Radio Liberty Kyrgyz service in Russian
    - **RT.com**: Russia Today international news in English

2. Click "Load News" to fetch articles

3. Browse through articles with pagination controls

### API Endpoint

The application also provides a REST API endpoint:

```bash
# Fetch news from a specific source
curl "http://localhost:8080/api.php?source=vesti"
curl "http://localhost:8080/api.php?source=azattyk"
curl "http://localhost:8080/api.php?source=rt"
```

**API Response Format:**
```json
{
  "articles": [
    {
      "title": "Article Title",
      "imageUrl": "https://example.com/image.jpg",
      "description": "Article description...",
      "sourceUrl": "https://example.com/article-link"
    }
  ],
  "count": 10,
  "source": "vesti"
}
```

### Project Structure

```
news-parser/
├── public/                   # Web root
│   ├── index.html            # Main web interface
│   ├── script.js             # Frontend JavaScript
│   ├── style.css             # Styling
│   └── api.php               # API endpoint
├── src/                      # PHP source code
│   ├── Contract/             # Interfaces
│   ├── DTO/                  # Data Transfer Objects
│   ├── Factory/              # Factory classes
│   ├── Parser/               # News parsers
│   └── Service/              # Services
└── config/            # Parser configurations
    └── parser_config.php     # CSS selectors and URLs
```


### Adding New News Sources

1. **Update parser configuration** in `config/parser_config.php`
2. **Create a new parser class** in `src/Parser/` extending `AbstractParser`
3. **Register the parser** in `src/Factory/ParserFactory.php`
4. **Add the source** to the frontend dropdown in `public/index.html`

Example parser configuration:
```php
'newsource' => [
    'listUrl' => 'https://example.com/news',
    'baseUrl' => 'https://example.com',
    'itemSelector' => '.news-item',
    'titleSelector' => '.title',
    'imageSelector' => 'img',
    'imageAttribute' => 'src',
    'descriptionSelector' => '.description',
    'linkSelector' => 'a',
    'linkAttribute' => 'href',
],
```

## Configuration

### Parser Configuration

CSS selectors and URLs are configured in `config/parser_config.php`. Update these if websites change their structure.

### Network Configuration

- **Port**: Application runs on port 8080 by default
- **Change port**: Modify the `ports` section in `docker-compose.yml`

### Performance Tuning

- **Timeout settings**: Adjust in `src/Service/HttpClientService.php`
- **Pagination**: Modify `ITEMS_PER_PAGE` in `public/script.js`
- **Memory limits**: Configure in `Dockerfile.php`
