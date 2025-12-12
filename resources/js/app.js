import './bootstrap';
import Chart from 'chart.js/auto';
import 'chartjs-adapter-date-fns';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Import Leaflet marker icons
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Configure Leaflet to use the standard default marker icons
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

// Make Chart available globally for blade templates
window.Chart = Chart;

// Make Leaflet available globally for blade templates
window.L = L;
