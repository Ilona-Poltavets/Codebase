import '../css/app.css';
import './bootstrap';

import Alpine from 'alpinejs';
import { createApp } from 'vue';
import TicketBoardApp from './components/TicketBoardApp.vue';

window.Alpine = Alpine;

Alpine.start();

const boardEl = document.getElementById('ticket-board-app');
if (boardEl) {
    createApp(TicketBoardApp, {
        fetchUrl: boardEl.dataset.fetchUrl,
        moveUrlTemplate: boardEl.dataset.moveUrlTemplate,
        ticketUrlTemplate: boardEl.dataset.ticketUrlTemplate,
        createTicketUrl: boardEl.dataset.createTicketUrl,
        initialView: boardEl.dataset.initialView || 'project',
    }).mount(boardEl);
}
