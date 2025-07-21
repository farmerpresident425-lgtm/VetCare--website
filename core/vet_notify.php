<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Send Notification</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

  <form method="POST" action="send_notifications.php" class="bg-white p-6 rounded-xl shadow-xl w-full max-w-lg">
    <h2 class="text-2xl font-bold mb-4 text-center">Send Email Notification</h2>

    <?php if (!empty($_SESSION['success'])): ?>
      <div class="bg-green-100 text-green-700 p-2 rounded mb-4 text-center">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
    <?php elseif (!empty($_SESSION['error'])): ?>
      <div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-center">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>

    <label class="block mb-2 font-medium">Send to:</label>
    <select name="notify_type" required class="w-full mb-4 p-2 border rounded">
      <option value="all">All Residents</option>
      <option value="individual">Specific Email</option>
    </select>

    <input type="email" name="email" placeholder="Email address (if individual)" class="w-full mb-4 p-2 border rounded">

    <input type="text" name="subject" placeholder="Subject" required class="w-full mb-4 p-2 border rounded">
    <textarea name="message" placeholder="Your message" required class="w-full mb-4 p-2 border rounded h-32"></textarea>

    <button type="submit" class="bg-blue-600 text-white w-full py-2 rounded hover:bg-blue-700">Send Notification</button>
  </form>

</body>
</html>
