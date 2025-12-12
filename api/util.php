<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\JsonStore;
use App\Models\BookingModel;
use App\Models\MovieModel;
use App\Models\SeatHoldModel;
use App\Models\UserModel;
use App\Services\AuthService;
use App\Services\BookingService;
use App\Controllers\Api\AuthApiController;
use App\Controllers\Api\MoviesApiController;
use App\Controllers\Api\BookingApiController;
use App\Controllers\Api\AdminApiController;

// Shared singletons for APIs
static $store;
static $movieModel;
static $bookingModel;
static $holdModel;
static $userModel;
static $authService;
static $bookingService;
static $authApi;
static $moviesApi;
static $bookingApi;
static $adminApi;

const MOVIES_FILE = DATA_PATH . '/movies.json';
const BOOKINGS_FILE = DATA_PATH . '/bookings.json';
const HOLDS_FILE = DATA_PATH . '/seat_holds.json';
const USERS_FILE = DATA_PATH . '/users.json';
const HOLD_TTL_SECONDS = BookingService::HOLD_TTL_SECONDS;

function store(): JsonStore
{
    global $store;
    if (!$store) {
        $store = new JsonStore(DATA_PATH);
    }
    return $store;
}

function movie_model(): MovieModel
{
    global $movieModel;
    if (!$movieModel) {
        $movieModel = new MovieModel(store(), MOVIES_FILE);
    }
    return $movieModel;
}

function booking_model(): BookingModel
{
    global $bookingModel;
    if (!$bookingModel) {
        $bookingModel = new BookingModel(store(), BOOKINGS_FILE);
    }
    return $bookingModel;
}

function hold_model(): SeatHoldModel
{
    global $holdModel;
    if (!$holdModel) {
        $holdModel = new SeatHoldModel(store(), HOLDS_FILE);
    }
    return $holdModel;
}

function user_model(): UserModel
{
    global $userModel;
    if (!$userModel) {
        $userModel = new UserModel(store(), USERS_FILE);
    }
    return $userModel;
}

function auth_service(): AuthService
{
    global $authService;
    if (!$authService) {
        $authService = new AuthService(user_model());
    }
    return $authService;
}

function booking_service(): BookingService
{
    global $bookingService;
    if (!$bookingService) {
        $bookingService = new BookingService(movie_model(), hold_model(), booking_model(), auth_service());
    }
    return $bookingService;
}

function auth_api(): AuthApiController
{
    global $authApi;
    if (!$authApi) {
        $authApi = new AuthApiController(auth_service());
    }
    return $authApi;
}

function movies_api(): MoviesApiController
{
    global $moviesApi;
    if (!$moviesApi) {
        $moviesApi = new MoviesApiController(movie_model());
    }
    return $moviesApi;
}

function booking_api(): BookingApiController
{
    global $bookingApi;
    if (!$bookingApi) {
        $bookingApi = new BookingApiController(booking_service());
    }
    return $bookingApi;
}

function admin_api(): AdminApiController
{
    global $adminApi;
    if (!$adminApi) {
        $adminApi = new AdminApiController(booking_service());
    }
    return $adminApi;
}

// Backwards-compatible helpers
function read_json(string $file, $default = [])
{
    return store()->read($file, $default);
}

function write_json(string $file, $data): bool
{
    return store()->write($file, $data);
}

function seat_template(): array
{
    return booking_service()->seatTemplate();
}

function cleanup_expired_holds(array $holds, int $now): array
{
    return booking_service()->cleanupExpiredHolds($holds, $now);
}

function current_session_id(): string
{
    return session_id();
}

function current_user(): ?array
{
    return auth_service()->currentUser();
}

function require_login(): void
{
    auth_service()->requireLoginJson();
}

function require_admin(): void
{
    auth_service()->requireAdminJson();
}

