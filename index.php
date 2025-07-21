<?php
session_start();
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            if ($user['status'] === 'approved') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];

                if ($user['role'] === 'admin') {
                    header("Location: Admin/main.php");
                } elseif ($user['role'] === 'veterinarian') {
                    header("Location: Veterinarian/main.php");
                } elseif ($user['role'] === 'resident') {
                    header("Location: Resident/main.php");
                }
                exit;
            } else {
                $_SESSION['error'] = "Account pending approval.";
            }
        } else {
            $_SESSION['error'] = "Incorrect password.";
        }
    } else {
        $_SESSION['error'] = "No user found.";
    }

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VetCare+ | Welcome</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
   .floating-title {
  position: absolute;
  top: -30px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 8rem;
  font-weight: 900;
  color: #047857;
  text-shadow: -2px -2px 0 #fff, 2px -2px 0 #fff, -2px 2px 0 #fff, 2px 2px 0 #fff;
  z-index: 50;
  pointer-events: none;
}

  </style>
</head>
<body class="relative min-h-screen overflow-hidden">

  <!-- Background -->
  <div class="fixed inset-0 z-0 overflow-hidden">
    <img src="assets/images/animal.jpg" alt="Animals" class="w-full h-full object-cover blur-md" />
  </div>

  <!-- Title -->
  <!-- Title (Responsive Text) -->
<div class="absolute top-4 left-1/2 transform -translate-x-1/2 text-4xl sm:text-6xl md:text-8xl font-extrabold text-green-700 bg-gradient-to-r from-slate-800 via-gray-700 to-slate-800 text-transparent bg-clip-text z-50 pointer-events-none select-none drop-shadow-md">
  VetCare+
</div>


  <!-- Flash error -->
  <?php if (isset($_SESSION['error'])): ?>
    <div class="absolute top-24 left-1/2 transform -translate-x-1/2 w-full max-w-sm bg-red-100 text-red-700 text-center text-sm py-2 px-4 rounded shadow z-50">
      <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <!-- Buttons: Left on large screens, Centered on small screens -->
 <div class="absolute top-1/2 left-1/2 sm:left-10 transform -translate-x-1/2 sm:translate-x-0 -translate-y-1/2 z-20 flex flex-col space-y-4 items-center sm:items-start w-full sm:w-48">
  <button onclick="showForm()"
    class="w-36 sm:w-full bg-blue-600 text-white text-sm sm:text-lg py-2 sm:py-3 rounded-full hover:bg-blue-700 font-semibold shadow-lg">
    Login
  </button>
  <button onclick="showRegisterForm()"
    class="w-36 sm:w-full bg-green-500 text-white text-sm sm:text-lg py-2 sm:py-3 rounded-full hover:bg-green-600 font-semibold shadow-lg">
    Sign Up
  </button>
</div>




  <!-- Login Box -->
  <div id="loginBox" class="hidden absolute z-30 bg-white bg-opacity-90 backdrop-blur-md p-6 sm:p-8 rounded-xl shadow-lg border border-gray-300 w-11/12 max-w-md"
       style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <form method="POST" action="" class="space-y-4">
      <select name="role" required class="w-full px-4 py-2 rounded-full text-blue-800 placeholder-blue-800 border border-blue-300 text-sm">
        <option value="">-- Select Role --</option>
        <option value="resident">Resident</option>
        <option value="veterinarian">Veterinarian</option>
        <option value="admin">Admin</option>
      </select>

      <input type="text" name="username" placeholder="Username" required class="w-full px-4 py-2 border border-blue-300 rounded-full text-sm text-blue-800 placeholder-blue-800">
      
      <input type="password" name="password" id="password" placeholder="Password" required class="w-full px-4 py-2 border border-blue-300 rounded-full text-sm text-blue-800 placeholder-blue-800">

      <div class="flex justify-center">
        <button type="submit" class="bg-blue-600 text-white px-5 py-2 text-sm border border-blue-300 rounded-full hover:bg-blue-700 transition">
          Continue
        </button>
      </div>
    </form>
  </div>

  <!-- Register Box -->
  <div id="registerBox" class="hidden absolute z-20 bg-white bg-opacity-90 backdrop-blur-md p-6 sm:p-8 rounded-xl shadow-lg border border-gray-300 w-11/12 max-w-md"
       style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <form method="POST" action="auth/register.php" class="space-y-2">
      <h2 class="text-lg font-bold text-blue-700 text-center mb-2">Create Your Account</h2>

      <input type="text" name="name" placeholder="Full Name" required class="w-full border border-blue-300 rounded-full px-4 py-2 text-sm text-blue-800 placeholder-blue-800">
      <input type="email" name="email" placeholder="Email Address" required class="w-full border border-blue-300 rounded-full px-4 py-2 text-sm text-blue-800 placeholder-blue-800">
      <input type="date" name="birthdate" required class="w-full border border-blue-300 rounded-full px-4 py-2 text-sm text-blue-800">
      <input type="text" name="address" placeholder="Address" required class="w-full border border-blue-300 rounded-full px-4 py-2 text-sm text-blue-800 placeholder-blue-800">
      <input type="text" name="purok" placeholder="Purok" required class="w-full border border-blue-300 rounded-full px-4 py-2 text-sm text-blue-800 placeholder-blue-800">
      <input type="text" name="username" placeholder="Username" required class="w-full border border-blue-300 rounded-full px-4 py-2 text-sm text-blue-800 placeholder-blue-800">
      <input type="password" name="password" placeholder="Password" required class="w-full border border-blue-300 rounded-full px-4 py-2 text-sm text-blue-800 placeholder-blue-800">

      <select name="role" required class="w-full border border-blue-300 rounded-full px-4 py-2 text-sm text-blue-800">
        <option value="">-- Select Role --</option>
        <option value="resident">Resident</option>
        <option value="veterinarian">Veterinarian</option>
        <option value="admin">Admin</option>
      </select>

      <div class="flex justify-center">
        <button type="submit" class="bg-green-500 text-white px-5 py-2 text-sm rounded-full hover:bg-green-600 transition">
          Register
        </button>
      </div>
    </form>
  </div>

  <!-- JS to toggle login/register -->
  <script>
  function showForm() {
    const loginBox = document.getElementById('loginBox');
    const registerBox = document.getElementById('registerBox');

    // If login box is already visible, hide it
    if (!loginBox.classList.contains('hidden')) {
      loginBox.classList.add('hidden');
    } else {
      loginBox.classList.remove('hidden');
      registerBox.classList.add('hidden');
    }
  }

  function showRegisterForm() {
    const registerBox = document.getElementById('registerBox');
    const loginBox = document.getElementById('loginBox');

    // If register box is already visible, hide it
    if (!registerBox.classList.contains('hidden')) {
      registerBox.classList.add('hidden');
    } else {
      registerBox.classList.remove('hidden');
      loginBox.classList.add('hidden');
    }
  }
</script>


</body>
</html>
