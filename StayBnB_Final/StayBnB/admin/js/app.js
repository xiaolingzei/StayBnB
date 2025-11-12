// Basic dashboard mock data fetcher
document.addEventListener("DOMContentLoaded", () => {
  const hotels = 8;
  const bookings = 15;
  const users = 23;

  document.getElementById("totalHotels").textContent = hotels;
  document.getElementById("totalBookings").textContent = bookings;
  document.getElementById("totalUsers").textContent = users;

  const tbody = document.getElementById("recentBookings");
  tbody.innerHTML = `
    <tr><td>Maria Lopez</td><td>Vista Mar Hotel</td><td>2025-10-12</td><td>2025-10-14</td><td>Confirmed</td></tr>
    <tr><td>John Reyes</td><td>Sunset Resort</td><td>2025-10-15</td><td>2025-10-17</td><td>Pending</td></tr>
    <tr><td>Ana Cruz</td><td>Blue Coast Inn</td><td>2025-10-10</td><td>2025-10-12</td><td>Cancelled</td></tr>
  `;
});
