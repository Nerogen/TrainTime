const burger = document.getElementById('burger');
const sidebar = document.getElementById('sidebar');
const backdrop = document.getElementById('backdrop');

const toggleMenu = (open) => {
    sidebar.hidden = !open;
    backdrop.hidden = !open;
    burger.setAttribute('aria-expanded', String(open));
};

burger?.addEventListener('click', () => {
    toggleMenu(burger.getAttribute('aria-expanded') !== 'true');
});

backdrop?.addEventListener('click', () => toggleMenu(false));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        toggleMenu(false);
    }
});

const picker = document.getElementById('picker');

picker?.addEventListener('click', (event) => {
    const button = event.target.closest('button');

    if (!button) {
        return;
    }

    const days = [...document.querySelectorAll('.months input[type="checkbox"]')];

    if (button.hasAttribute('data-clear')) {
        days.forEach((day) => (day.checked = false));

        return;
    }

    const weekday = Number(button.dataset.weekday);
    const column = days.filter((day) => (new Date(day.value + 'T00:00').getDay() + 6) % 7 === weekday);
    const turnOn = column.some((day) => !day.checked);

    column.forEach((day) => (day.checked = turnOn));
});
