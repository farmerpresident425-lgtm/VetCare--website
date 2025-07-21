<?php

include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../../index.php");
    exit;
}

$residentId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM animals WHERE owner_id = ?");
$stmt->bind_param("i", $residentId);
$stmt->execute();
$result = $stmt->get_result();
$animals = [];
while ($row = $result->fetch_assoc()) {
    $animals[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Animal Appointments</title>
</head>
<body class="bg-gray-100 py-8 px-4">

<!-- Search Bar Container -->
<div class="w-full flex justify-start mb-4">
  <input id="searchAnimal" type="text" placeholder="Search animal name..." 
    class="w-[400px] px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring focus:border-blue-300">
</div>

<!-- Animal Cards Container -->
<div id="animalList" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
  <?php foreach ($animals as $animal): ?>
    <div class="animal-card bg-white p-5 rounded-lg shadow transition-all duration-300">
      <h3 class="text-xl font-semibold mb-1"><?= htmlspecialchars($animal['animal_name']) ?></h3>
      <p class="text-gray-600 mb-1"><strong>Breed:</strong> <?= htmlspecialchars($animal['breed']) ?></p>
      <p class="text-gray-600 mb-1"><strong>Sex:</strong> <?= htmlspecialchars($animal['sex']) ?></p>
      <p class="text-gray-600 mb-1"><strong>Color:</strong> <?= htmlspecialchars($animal['color']) ?></p>
      <p class="text-gray-600 mb-3"><strong>Weight:</strong> <?= htmlspecialchars($animal['weight_kg']) ?> kg</p>

      <!-- Request Appointment Button -->
      <button onclick="toggleForm(<?= $animal['id'] ?>)" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Request Appointment
      </button>

      <!-- Appointment Form -->
      <div id="form-<?= $animal['id'] ?>" class="form-section opacity-0 pointer-events-none mt-1 transition-all duration-300">
  <form onsubmit="submitAppointment(event, <?= $animal['id'] ?>)" class="space-y-3">
    <input type="date" name="appointment_date" required class="w-full border rounded px-3 py-2" />
    <input type="time" name="appointment_time" required class="w-full border rounded px-3 py-2" />
    <textarea name="purpose" required placeholder="Purpose of appointment" class="w-full border rounded px-3 py-2"></textarea>
    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Submit</button>
  </form>
</div>

    </div>
  <?php endforeach; ?>
</div>


<!-- Scripts -->
<script>
// 🔍 Search by Animal Name
document.getElementById('searchAnimal').addEventListener('input', function () {
  const searchTerm = this.value.toLowerCase().trim();
  const cards = document.querySelectorAll('#animalList .animal-card');

  cards.forEach(card => {
    const name = card.querySelector('h3').textContent.toLowerCase();
    card.style.display = name.includes(searchTerm) ? '' : 'none';
  });
});

// ✅ Toggle specific form only
function toggleForm(id) {
  document.querySelectorAll('[id^="form-"]').forEach(form => {
    if (form.id !== `form-${id}`) {
      form.classList.add('opacity-0', 'pointer-events-none');
    }
  });

  const selectedForm = document.getElementById(`form-${id}`);
  selectedForm.classList.toggle('opacity-0');
  selectedForm.classList.toggle('pointer-events-none');
}


  

// ✅ Submit appointment via fetch
function submitAppointment(event, animalId) {
  event.preventDefault();

  const form = document.querySelector(`#form-${animalId} form`);
  const date = form.querySelector('[name="appointment_date"]').value;
  const time = form.querySelector('[name="appointment_time"]').value;
  const purpose = form.querySelector('[name="purpose"]').value;

  const data = new FormData();
  data.append("animal_id", animalId);
  data.append("appointment_date", date);
  data.append("appointment_time", time);
  data.append("purpose", purpose);

  fetch("submit_appointment.php", {
    method: "POST",
    body: data,
  })
  .then(res => res.text())
  .then(response => {
    alert(response.trim() === 'success' ? "Appointment request sent!" : response);
    form.reset();
    document.getElementById(`form-${animalId}`).classList.add("hidden");
  })
  .catch(error => {
    alert("Error sending request.");
    console.error(error);
  });
}
</script>

</body>
</html>
