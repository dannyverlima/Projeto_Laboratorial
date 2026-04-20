const weatherCodeMap = {
  0: "Ceo limpo",
  1: "Poucas nuvens",
  2: "Parcialmente nublado",
  3: "Nublado",
  45: "Nevoeiro",
  48: "Nevoeiro denso",
  51: "Chuvisco fraco",
  53: "Chuvisco moderado",
  55: "Chuvisco forte",
  61: "Chuva fraca",
  63: "Chuva moderada",
  65: "Chuva forte",
  71: "Neve fraca",
  73: "Neve moderada",
  75: "Neve forte",
  80: "Aguaceiros fracos",
  81: "Aguaceiros moderados",
  82: "Aguaceiros fortes",
  95: "Trovoada"
};

function setMiniChartBars() {
  const bars = document.querySelectorAll(".mini-chart .bar");
  bars.forEach((bar) => {
    const val = Number(bar.dataset.value || 0);
    bar.style.height = `${Math.max(8, Math.min(100, val))}%`;
  });
}

function classifyAirQuality(co2ppm) {
  if (co2ppm <= 900) {
    return { label: "Boa", className: "status-good" };
  }
  return { label: "Atencao", className: "status-warn" };
}

function updateAirStatusFromRoomData() {
  const roomNodes = document.querySelectorAll("[data-room-co2]");
  if (!roomNodes.length) {
    return;
  }

  const values = [...roomNodes].map((node) => Number(node.dataset.roomCo2 || 0));
  const average = values.reduce((sum, value) => sum + value, 0) / values.length;
  const status = classifyAirQuality(average);

  const badge = document.getElementById("air-status");
  if (!badge) {
    return;
  }

  badge.textContent = `Qualidade do ar: ${status.label}`;
  badge.classList.remove("status-good", "status-warn");
  badge.classList.add(status.className);
}

async function fetchPombalWeather() {
  const endpoint = "https://api.open-meteo.com/v1/forecast?latitude=39.9167&longitude=-8.6333&current=temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code&timezone=Europe%2FLisbon";

  try {
    const response = await fetch(endpoint);
    if (!response.ok) {
      throw new Error("Falha ao obter meteorologia");
    }

    const payload = await response.json();
    const current = payload.current || {};

    const tempNode = document.getElementById("weather-temp");
    const descNode = document.getElementById("weather-desc");
    const humidNode = document.getElementById("weather-humidity");
    const windNode = document.getElementById("weather-wind");
    const updateNode = document.getElementById("weather-updated");

    if (!tempNode || !descNode || !humidNode || !windNode || !updateNode) {
      return;
    }

    const code = Number(current.weather_code);
    tempNode.textContent = `${Math.round(current.temperature_2m)} C`;
    descNode.textContent = weatherCodeMap[code] || "Condição sem descricao";
    humidNode.textContent = `Humidade: ${current.relative_humidity_2m}%`;
    windNode.textContent = `Vento: ${current.wind_speed_10m} km/h`;

    const now = new Date();
    updateNode.textContent = `Atualizado: ${now.toLocaleString("pt-PT")}`;
  } catch (error) {
    const tempNode = document.getElementById("weather-temp");
    const descNode = document.getElementById("weather-desc");
    const updateNode = document.getElementById("weather-updated");

    if (tempNode) {
      tempNode.textContent = "--";
    }
    if (descNode) {
      descNode.textContent = "Meteorologia indisponivel";
    }
    if (updateNode) {
      updateNode.textContent = "Atualizado: sem ligacao";
    }

    console.error(error);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  setMiniChartBars();
  updateAirStatusFromRoomData();
  fetchPombalWeather();
});
