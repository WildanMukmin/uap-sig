// Global variables
let map;
let markersLayer;
let kecamatanLayer;
let allData = [];
let filteredData = [];

const API_URL = 'api/';
const GEOJSON_URL = 'data/kecamatan.geojson';

// Initialize map
function initMap() {
    map = L.map('map').setView([-5.4292, 105.2619], 12);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    kecamatanLayer = L.layerGroup().addTo(map);
    markersLayer = L.layerGroup().addTo(map);
    
    loadKecamatanBoundaries();
    loadData();
}

// Load kecamatan boundaries from GeoJSON
async function loadKecamatanBoundaries() {
    try {
        const response = await fetch(GEOJSON_URL);
        const geojson = await response.json();
        
        // Style function for polygons
        function style(feature) {
            return {
                fillColor: getColorByKecamatan(feature.properties.Kecamatan),
                weight: 2,
                opacity: 1,
                color: '#2563eb',
                dashArray: '3',
                fillOpacity: 0.3
            };
        }
        
        // Highlight style
        function highlightFeature(e) {
            const layer = e.target;
            
            layer.setStyle({
                weight: 3,
                color: '#1e40af',
                dashArray: '',
                fillOpacity: 0.5
            });
            
            layer.bringToFront();
            markersLayer.bringToFront();
        }
        
        // Reset style
        function resetHighlight(e) {
            kecamatanGeoJSON.resetStyle(e.target);
        }
        
        // Click handler
        function onEachFeature(feature, layer) {
            const kecamatan = feature.properties.Kecamatan || 'Tidak diketahui';
            const luas = feature.properties.Luas_km || '-';
            
            layer.bindTooltip(`<strong>${kecamatan}</strong><br>Luas: ${luas} km²`, {
                permanent: false,
                direction: 'center',
                className: 'kecamatan-tooltip'
            });
            
            layer.on({
                mouseover: highlightFeature,
                mouseout: resetHighlight,
                click: function(e) {
                    map.fitBounds(e.target.getBounds());
                    document.getElementById('filter-kecamatan').value = kecamatan;
                    applyFilters();
                }
            });
        }
        
        // Add GeoJSON to map
        const kecamatanGeoJSON = L.geoJSON(geojson, {
            style: style,
            onEachFeature: onEachFeature
        }).addTo(kecamatanLayer);
        
        console.log('Kecamatan boundaries loaded successfully');
        
    } catch (error) {
        console.error('Error loading kecamatan boundaries:', error);
    }
}

// Get color by kecamatan name
function getColorByKecamatan(kecamatan) {
    const colors = {
        'Kemiling': '#ef4444',
        'Teluk Betung Barat': '#f97316',
        'Teluk Betung Timur': '#f59e0b',
        'Teluk Betung Selatan': '#eab308',
        'Bumi Waras': '#84cc16',
        'Panjang': '#22c55e',
        'Sukabumi': '#10b981',
        'Sukarame': '#14b8a6',
        'Tanjung Senang': '#06b6d4',
        'Rajabasa': '#0ea5e9',
        'Langkapura': '#3b82f6',
        'Labuhan Ratu': '#6366f1',
        'Kedaton': '#8b5cf6',
        'Way Halim': '#a855f7',
        'Kedamaian': '#d946ef',
        'Teluk Betung Utara': '#ec4899',
        'Enggal': '#f43f5e',
        'Tanjung Karang Timur': '#64748b',
        'Tanjung Karang Pusat': '#71717a',
        'Tanjung Karang Barat': '#78716c'
    };
    
    return colors[kecamatan] || '#94a3b8';
}

// Load data from API
async function loadData() {
    try {
        const response = await fetch(API_URL + 'get_data.php');
        const geojson = await response.json();
        
        if (geojson && geojson.features) {
            allData = geojson.features;
            filteredData = [...allData];
            displayMarkers(filteredData);
            displayList(filteredData);
            populateFilters();
            updateStatistics();
            updateDataCount();
        }
    } catch (error) {
        console.error('Error loading data:', error);
        showError('Gagal memuat data');
    }
}

// Display markers on map
function displayMarkers(data) {
    markersLayer.clearLayers();
    
    data.forEach(feature => {
        const props = feature.properties;
        const coords = feature.geometry.coordinates;
        
        const icon = getIconByJenis(props.jenis);
        
        const marker = L.marker([coords[1], coords[0]], { icon: icon })
            .bindPopup(createPopupContent(props), {
                maxWidth: 300,
                className: 'custom-popup'
            })
            .addTo(markersLayer);
        
        marker.feature = feature;
    });
    
    // Bring markers to front
    markersLayer.bringToFront();
}

// Get icon by jenis
function getIconByJenis(jenis) {
    const iconColors = {
        'Masjid': '#10b981',
        'Gereja': '#3b82f6',
        'Pura': '#f59e0b',
        'Vihara': '#ec4899',
        'Klenteng': '#8b5cf6'
    };
    
    const iconSymbols = {
        'Masjid': 'fa-mosque',
        'Gereja': 'fa-church',
        'Pura': 'fa-place-of-worship',
        'Vihara': 'fa-om',
        'Klenteng': 'fa-yin-yang'
    };
    
    const color = iconColors[jenis] || '#6b7280';
    const symbol = iconSymbols[jenis] || 'fa-place-of-worship';
    
    return L.divIcon({
        className: 'custom-marker',
        html: `<div style="background-color: ${color}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                <i class="fas ${symbol}" style="color: white; font-size: 14px;"></i>
               </div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });
}

// Create popup content
function createPopupContent(props) {
    return `
        <div class="p-2">
            <h3 class="font-bold text-lg text-gray-800 mb-2">${props.nama}</h3>
            <div class="space-y-1 text-sm">
                <p><span class="font-semibold">Jenis:</span> ${props.jenis}</p>
                <p><span class="font-semibold">Alamat:</span> ${props.alamat || '-'}</p>
                <p><span class="font-semibold">Kecamatan:</span> ${props.kecamatan || '-'}</p>
                <p><span class="font-semibold">Kapasitas:</span> ${props.kapasitas || '-'} orang</p>
                <p><span class="font-semibold">Tahun Berdiri:</span> ${props.tahun_berdiri || '-'}</p>
                ${props.keterangan ? `<p class="mt-2 text-gray-600">${props.keterangan}</p>` : ''}
            </div>
        </div>
    `;
}

// Display list
function displayList(data) {
    const container = document.getElementById('list-container');
    
    if (data.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-4xl mb-2"></i>
                <p>Tidak ada data yang ditemukan</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = data.map(feature => {
        const props = feature.properties;
        const badgeColors = {
            'Masjid': 'bg-green-100 text-green-800',
            'Gereja': 'bg-blue-100 text-blue-800',
            'Pura': 'bg-yellow-100 text-yellow-800',
            'Vihara': 'bg-pink-100 text-pink-800',
            'Klenteng': 'bg-purple-100 text-purple-800'
        };
        
        return `
            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-lg transition cursor-pointer" onclick="focusMarker(${props.id})">
                <div class="flex items-start justify-between mb-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold ${badgeColors[props.jenis] || 'bg-gray-100 text-gray-800'}">
                        ${props.jenis}
                    </span>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">${props.nama}</h3>
                <div class="text-sm text-gray-600 space-y-1">
                    <p><i class="fas fa-map-marker-alt text-blue-500 w-4"></i> ${props.kecamatan || 'N/A'}</p>
                    <p><i class="fas fa-users text-blue-500 w-4"></i> Kapasitas: ${props.kapasitas || 'N/A'} orang</p>
                </div>
            </div>
        `;
    }).join('');
}

// Focus on marker
function focusMarker(id) {
    const feature = allData.find(f => f.properties.id === id);
    if (feature) {
        const coords = feature.geometry.coordinates;
        map.setView([coords[1], coords[0]], 16);
        
        markersLayer.eachLayer(layer => {
            if (layer.feature && layer.feature.properties.id === id) {
                layer.openPopup();
            }
        });
    }
}

// Populate filters
function populateFilters() {
    const kecamatans = [...new Set(allData.map(f => f.properties.kecamatan).filter(k => k))];
    const kecamatanSelect = document.getElementById('filter-kecamatan');
    
    kecamatanSelect.innerHTML = '<option value="">Semua</option>' + 
        kecamatans.sort().map(k => `<option value="${k}">${k}</option>`).join('');
}

// Apply filters
function applyFilters() {
    const jenisFilter = document.getElementById('filter-jenis').value;
    const kecamatanFilter = document.getElementById('filter-kecamatan').value;
    const searchText = document.getElementById('search-box').value.toLowerCase();
    
    filteredData = allData.filter(feature => {
        const props = feature.properties;
        
        const matchJenis = !jenisFilter || props.jenis === jenisFilter;
        const matchKecamatan = !kecamatanFilter || props.kecamatan === kecamatanFilter;
        const matchSearch = !searchText || props.nama.toLowerCase().includes(searchText);
        
        return matchJenis && matchKecamatan && matchSearch;
    });
    
    displayMarkers(filteredData);
    displayList(filteredData);
    updateDataCount();
}

// Update data count
function updateDataCount() {
    document.getElementById('data-count').textContent = filteredData.length;
}

// Update statistics
function updateStatistics() {
    const stats = {};
    allData.forEach(feature => {
        const jenis = feature.properties.jenis;
        stats[jenis] = (stats[jenis] || 0) + 1;
    });
    
    const colors = {
        'Masjid': 'from-green-400 to-green-600',
        'Gereja': 'from-blue-400 to-blue-600',
        'Pura': 'from-yellow-400 to-yellow-600',
        'Vihara': 'from-pink-400 to-pink-600',
        'Klenteng': 'from-purple-400 to-purple-600'
    };
    
    const icons = {
        'Masjid': 'fa-mosque',
        'Gereja': 'fa-church',
        'Pura': 'fa-place-of-worship',
        'Vihara': 'fa-om',
        'Klenteng': 'fa-yin-yang'
    };
    
    const container = document.getElementById('stats-container');
    container.innerHTML = `
        <div class="bg-gradient-to-br from-gray-400 to-gray-600 rounded-lg p-4 text-white text-center">
            <i class="fas fa-database text-3xl mb-2"></i>
            <p class="text-3xl font-bold">${allData.length}</p>
            <p class="text-sm">Total</p>
        </div>
        ${Object.entries(stats).map(([jenis, count]) => `
            <div class="bg-gradient-to-br ${colors[jenis] || 'from-gray-400 to-gray-600'} rounded-lg p-4 text-white text-center">
                <i class="fas ${icons[jenis] || 'fa-place-of-worship'} text-3xl mb-2"></i>
                <p class="text-3xl font-bold">${count}</p>
                <p class="text-sm">${jenis}</p>
            </div>
        `).join('')}
    `;
}

// Show error message
function showError(message) {
    const container = document.getElementById('list-container');
    container.innerHTML = `
        <div class="col-span-full text-center py-8 text-red-500">
            <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
            <p>${message}</p>
        </div>
    `;
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    
    document.getElementById('filter-jenis').addEventListener('change', applyFilters);
    document.getElementById('filter-kecamatan').addEventListener('change', applyFilters);
    document.getElementById('search-box').addEventListener('input', applyFilters);
});