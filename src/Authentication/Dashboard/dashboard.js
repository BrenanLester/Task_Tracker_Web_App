// dashboard.js

document.addEventListener('DOMContentLoaded', () => {

  const timeDisplay = document.getElementById('current-time');
  const fullDateDisplay = document.getElementById('current-full-date');
  const monthDisplay = document.getElementById('current-month');
  const yearDisplay = document.getElementById('current-year');
  const calendarGrid = document.getElementById('calendar-grid');

  // Calendar Navigation Elements
  const prevMonthBtn = document.querySelector('.bi-chevron-left');
  const nextMonthBtn = document.querySelector('.bi-chevron-right');

  // State variable to track the currently displayed month/year
  let currentDisplayDate = new Date();
  
  // --- Time and Date Update Function (runs every second) ---
  function updateDateTime() {
      const now = new Date();

      const timeOptions = { 
          hour: 'numeric', 
          minute: '2-digit', 
          second: '2-digit', 
          hour12: true 
      };
      timeDisplay.textContent = now.toLocaleTimeString('en-US', timeOptions);
      const dateOptions = { 
          weekday: 'long', 
          year: 'numeric', 
          month: 'long', 
          day: 'numeric' 
      };
      fullDateDisplay.textContent = now.toLocaleDateString('en-US', dateOptions);
      setTimeout(updateDateTime, 1000);
  }

  function generateCalendar(date) {
      const year = date.getFullYear();
      const month = date.getMonth(); // 0-11
      const today = new Date();
      const currentDayOfMonth = today.getDate();
      const isCurrentMonth = (year === today.getFullYear() && month === today.getMonth());


      const monthName = date.toLocaleDateString('en-US', { month: 'long' });
      monthDisplay.textContent = monthName;
      yearDisplay.textContent = year;
      const firstDayOfMonth = new Date(year, month, 1);
      const startingDayOfWeek = firstDayOfMonth.getDay(); 
      const daysInMonth = new Date(year, month + 1, 0).getDate(); 

      while (calendarGrid.children.length > 7) {
          calendarGrid.removeChild(calendarGrid.lastChild);
      }

      for (let i = 0; i < startingDayOfWeek; i++) {
          const blankDay = document.createElement('div');
          blankDay.className = 'blank-day'; 
          calendarGrid.appendChild(blankDay);
      }

      for (let day = 1; day <= daysInMonth; day++) {
          const dayElement = document.createElement('div');
          dayElement.className = 'day-number';
          dayElement.textContent = day;

        
          if (isCurrentMonth && day === currentDayOfMonth) {
              dayElement.classList.add('active-day');
          }

          calendarGrid.appendChild(dayElement);
      }
  }


  

  prevMonthBtn.addEventListener('click', () => {
      currentDisplayDate.setMonth(currentDisplayDate.getMonth() - 1);
      generateCalendar(currentDisplayDate);
  });


  nextMonthBtn.addEventListener('click', () => {
      currentDisplayDate.setMonth(currentDisplayDate.getMonth() + 1);
      generateCalendar(currentDisplayDate);
  });


  updateDateTime();
  generateCalendar(currentDisplayDate);
});