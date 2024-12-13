import "./bootstrap";
import { Fancybox } from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";
import TomSelect from "tom-select";
import "tom-select/dist/css/tom-select.css"; // or another theme if preferred
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import monthSelectPlugin from "flatpickr/dist/plugins/monthSelect";
import "flatpickr/dist/plugins/monthSelect/style.css";

window.Fancybox = Fancybox;
window.TomSelect = TomSelect;
window.flatpickr = flatpickr;
window.monthSelectPlugin = monthSelectPlugin;
