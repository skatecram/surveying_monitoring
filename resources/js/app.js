import './bootstrap';
import Chart from 'chart.js/auto';
import 'chartjs-adapter-date-fns';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Make Chart available globally for blade templates
window.Chart = Chart;

// Make Leaflet available globally for blade templates
window.L = L;
