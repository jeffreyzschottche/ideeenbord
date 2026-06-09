import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  ArcElement,
  PointElement,
  LineElement,
  CategoryScale,
  LinearScale,
  Filler,
} from "chart.js";

// Register the Chart.js building blocks used by the report charts once,
// client-side. vue-chartjs components rely on these being registered.
export default defineNuxtPlugin(() => {
  ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    ArcElement,
    PointElement,
    LineElement,
    CategoryScale,
    LinearScale,
    Filler
  );
});
