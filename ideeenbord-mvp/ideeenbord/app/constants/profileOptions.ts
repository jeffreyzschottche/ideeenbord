// Gedeelde keuzelijsten voor registratie en profiel/datavoorkeuren.

export const genderOptions = ["Man", "Vrouw", "Non-binair", "Anders", "Zeg ik liever niet"];

export const educationLevelOptions = [
  "Basisonderwijs",
  "VMBO",
  "HAVO",
  "VWO",
  "MBO",
  "HBO",
  "WO",
  "Anders",
];

export const relationshipOptions = [
  "Single",
  "Relatie",
  "Samenwonend",
  "Getrouwd",
  "Geregistreerd partnerschap",
  "Gescheiden",
  "Weduwe/weduwnaar",
  "Zeg ik liever niet",
];

export const sectorOptions = [
  "Detailhandel",
  "Horeca",
  "Zorg & welzijn",
  "Onderwijs",
  "ICT & techniek",
  "Financieel & verzekeringen",
  "Overheid",
  "Bouw",
  "Industrie & productie",
  "Transport & logistiek",
  "Marketing & communicatie",
  "Media & entertainment",
  "Landbouw & voeding",
  "Energie & milieu",
  "Vastgoed",
  "Juridisch",
  "Wetenschap & R&D",
  "Sport & recreatie",
  "Non-profit",
  "Student",
  "Werkzoekend",
  "Gepensioneerd",
  "Anders",
];

// ---- Optionele datavoorkeuren ----

export const politicalOptions = [
  "Links",
  "Midden-links",
  "Midden",
  "Midden-rechts",
  "Rechts",
  "Wisselend",
  "Geen voorkeur",
  "Zeg ik liever niet",
];

export const householdRoleOptions = [
  "Ik doe meestal de boodschappen",
  "We doen het samen",
  "Iemand anders doet ze",
  "Niet van toepassing",
];

export const purchaseDecisionOptions = [
  "Ik beslis meestal",
  "We beslissen samen",
  "Iemand anders beslist",
];

export const orderFrequencyOptions = [
  "(Bijna) dagelijks",
  "Wekelijks",
  "Maandelijks",
  "Een paar keer per jaar",
  "Zelden of nooit",
];

export const techSpendOptions = ["€0–25", "€25–75", "€75–150", "€150–300", "€300+"];

export const grocerySpendOptions = ["< €150", "€150–300", "€300–500", "€500–800", "€800+"];

export const householdSizeOptions = ["1", "2", "3", "4", "5 of meer"];

// Generieke definitie van de datavoorkeuren-vragen (voor het profielformulier).
export type DataProfileField = {
  key: string;
  label: string;
  options: string[];
  icon: string;
};

export const dataProfileFields: DataProfileField[] = [
  { key: "political_preference", label: "Politieke voorkeur", options: politicalOptions, icon: "fa-landmark" },
  { key: "household_role", label: "Wie doet de boodschappen in jouw huishouden?", options: householdRoleOptions, icon: "fa-basket-shopping" },
  { key: "purchase_decision", label: "Wie beslist over de aankopen thuis?", options: purchaseDecisionOptions, icon: "fa-cart-shopping" },
  { key: "order_frequency", label: "Hoe vaak bestel je online?", options: orderFrequencyOptions, icon: "fa-truck-fast" },
  { key: "tech_spend", label: "Maandelijkse uitgaven aan technologie", options: techSpendOptions, icon: "fa-microchip" },
  { key: "grocery_spend", label: "Maandelijkse uitgaven aan boodschappen", options: grocerySpendOptions, icon: "fa-cart-flatbed" },
  { key: "household_size", label: "Aantal personen in je huishouden", options: householdSizeOptions, icon: "fa-house-user" },
];
