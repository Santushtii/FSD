const API_URL = 'http://localhost:3000/movies';

const movieListDiv = document.getElementById('movie-list');
const searchInput = document.getElementById('search-input');
const form = document.getElementById('add-movie-form');

let allMovies = []; // Stores the full, unfiltered list of movies

// Function to dynamically render movies to the HTML
function renderMovies(moviesToDisplay) {
    movieListDiv.innerHTML = '';
    if (moviesToDisplay.length === 0) {
        movieListDiv.innerHTML = '<p>No movies found matching your criteria.</p>';
        return;
    }

    moviesToDisplay.forEach(movie => {
        const movieElement = document.createElement('div');
        movieElement.classList.add('movie-item');
        movieElement.innerHTML = `
            <p><strong>${movie.title}</strong> (${movie.year}) - ${movie.genre}</p>
            <button onclick="editMoviePrompt(${movie.id}, '${movie.title}', ${movie.year}, '${movie.genre}')">Edit</button>
            <button onclick="deleteMovie(${movie.id})">Delete</button>
        `;
        movieListDiv.appendChild(movieElement);
    });
}

// Function to fetch all movies and store them (READ)
function fetchMovies() {
    fetch(API_URL)
        .then(response => response.json())
        .then(movies => {
            allMovies = movies; // Store the full list
            renderMovies(allMovies); // Display the full list
        })
        .catch(error => console.error('Error fetching movies:', error));
}
fetchMovies(); // Initial load

// Search Functionality
searchInput.addEventListener('input', function () {
    const searchTerm = searchInput.value.toLowerCase();

    // Filter the global 'allMovies' array based on title or genre match
    const filteredMovies = allMovies.filter(movie => {
        const titleMatch = movie.title.toLowerCase().includes(searchTerm);
        const genreMatch = movie.genre.toLowerCase().includes(searchTerm);
        return titleMatch || genreMatch;
    });

    renderMovies(filteredMovies); // Display the filtered results
});

// CREATE Operation (POST Method)
form.addEventListener('submit', function (event) {
    event.preventDefault();

    const newMovie = {
        title: document.getElementById('title').value,
        genre: document.getElementById('genre').value,
        year: parseInt(document.getElementById('year').value)
    };

    fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newMovie),
    })
        .then(response => {
            if (!response.ok) throw new Error('Failed to add movie');
            return response.json();
        })
        .then(() => {
            form.reset();
            fetchMovies(); // Refresh the list
        })
        .catch(error => console.error('Error adding movie:', error));
});

// Function to collect new data (EDIT)
function editMoviePrompt(id, currentTitle, currentYear, currentGenre) {
    const newTitle = prompt('Enter new Title:', currentTitle);
    const newYearStr = prompt('Enter new Year:', currentYear);
    const newGenre = prompt('Enter new Genre:', currentGenre);

    if (newTitle && newYearStr && newGenre) {
        const updatedMovie = {
            id: id,
            title: newTitle,
            year: parseInt(newYearStr),
            genre: newGenre
        };
        updateMovie(id, updatedMovie);
    }
}

// UPDATE Operation (PUT Method)
function updateMovie(movieId, updatedMovieData) {
    fetch(`${API_URL}/${movieId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updatedMovieData),
    })
        .then(response => {
            if (!response.ok) throw new Error('Failed to update movie');
            return response.json();
        })
        .then(() => {
            fetchMovies(); // Refresh list
        })
        .catch(error => console.error('Error updating movie:', error));
}

// DELETE Operation (DELETE Method)
function deleteMovie(movieId) {
    fetch(`${API_URL}/${movieId}`, {
        method: 'DELETE',
    })
        .then(response => {
            if (!response.ok) throw new Error('Failed to delete movie');
            fetchMovies(); // Refresh list
        })
        .catch(error => console.error('Error deleting movie:', error));
}
