// Global variables
let map;
let markersLayer;
let kecamatanLayer;
let allData = [];
let filteredData = [];
let tempMarker = null;
let isEditMode = false;
let currentEditId = null;

const API_URL = 'api/';
const GEOJSON_URL = 'data/kecamatan.geojson';

// Check authentication on page load
async function checkAuth() {
    try {
        const response = await fetch(API_URL + 'auth.php?action=check');
        const result = await response.json();
        
        if (!result.success || !result.data.logged_in || result.data.user.role !== 'admin') {
            window.location.href = 'admin-login.php';
            return false;
        }
        
        // Display user info
        document.getElementById('user-name').textContent = result.data.user.full_name;
        document.getElementById('user-role').textContent = result.data.user.role.toUpperCase();
        
        return true;
    } catch (error) {
        console.error('Auth check error:', error);
        window.location.href = 'admin-login.php';
        return false;
    }
}

// Logout handler
async function handleLogout() {
    if (!confirm('Apakah Anda yakin ingin logout?')) return;
    
    try {
        await fetch(API_URL + 'auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'logout' })
        });
        
        window.location.href = 'admin-login.php';
    } catch (error) {
        console.error('Logout error:', error);
        alert('Terjadi kesalahan saat logout');
    }
}

// Initialize map
function initMap() {
    map = L.map('map').setView([-5.4292, 105.2619], 12);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    kecamatanLayer = L.layerGroup().addTo(map);
    markersLayer = L.layerGroup().addTo(map);
    
    // Add click event for adding new markers
    map.on('click', onMapClick);
    
    loadKecamatanBoundaries();
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
                fillOpacity: 0.2
            };
        }
        
        // Highlight style
        function highlightFeature(e) {
            const layer = e.target;
            
            layer.setStyle({
                weight: 3,
                color: '#1e40af',
                dashArray: '',
                fillOpacity: 0.4
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
                mouseout: resetHighlight
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

// Map click handler
function onMapClick(e) {
    const { lat, lng } = e.latlng;
    
    // Validate coordinates (Bandar Lampung area)
    if (lat < -5.5 || lat > -5.3 || lng < 105.1 || lng > 105.4) {
        alert('Koordinat di luar wilayah Bandar Lampung');
        return;
    }
    
    // Remove existing temp marker
    if (tempMarker) {
        map.removeLayer(tempMarker);
    }
    
    // Create temp marker
    tempMarker = L.marker([lat, lng], {
        icon: L.divIcon({
            className: 'temp-marker',
            html: '<div style="background-color: #ef4444; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        })
    }).addTo(map);
    
    showAddModal(lat, lng);
}

// Load data
async function loadData() {
    try {
        const response = await fetch(API_URL + 'get_data.php');
        const geojson = await response.json();
        
        if (geojson && geojson.features) {
            allData = geojson.features;
            filteredData = [...allData];
            displayMarkers(filteredData);
            displayTable(filteredData);
            populateFilters();
            updateStatistics();
            updateDataCount();
        }
    } catch (error) {
        console.error('Error loading data:', error);
        showNotification('Gagal memuat data' + error, 'error');
    }
}

// Display markers
function displayMarkers(data) {
    markersLayer.clearLayers();
    
    data.forEach(feature => {
        const props = feature.properties;
        const coords = feature.geometry.coordinates;
        
        const icon = getIconByJenis(props.jenis);
        
        const marker = L.marker([coords[1], coords[0]], { icon: icon })
            .bindPopup(createPopupContent(props), {
                maxWidth: 300,
                className: 'custom-popup',
                autoPan: true,
                autoPanPadding: [50, 50]
            })
            .addTo(markersLayer);
    });
    
    // // Bring markers to front so they appear above kecamatan layer
    // markersLayer.bringToFront();
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
            <h3 class="font-bold text-lg mb-2">${props.nama}</h3>
            <div class="space-y-1 text-sm mb-3">
                <p><strong>Jenis:</strong> ${props.jenis}</p>
                <p><strong>Kecamatan:</strong> ${props.kecamatan || '-'}</p>
                <p><strong>Kapasitas:</strong> ${props.kapasitas || '-'} orang</p>
            </div>
            <div class="flex gap-2">
                <button onclick="editData(${props.id})" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                    <i class="fas fa-edit mr-1"></i>Edit
                </button>
                <button onclick="deleteData(${props.id})" class="flex-1 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                    <i class="fas fa-trash mr-1"></i>Hapus
                </button>
            </div>
        </div>
    `;
}

// Display table
function displayTable(data) {
    const tbody = document.getElementById('table-body');
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                    Tidak ada data
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = data.map((feature, index) => {
        const props = feature.properties;
        const badgeColors = {
            'Masjid': 'bg-green-100 text-green-800',
            'Gereja': 'bg-blue-100 text-blue-800',
            'Pura': 'bg-yellow-100 text-yellow-800',
            'Vihara': 'bg-pink-100 text-pink-800',
            'Klenteng': 'bg-purple-100 text-purple-800'
        };
        
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm">${index + 1}</td>
                <td class="px-4 py-3 text-sm font-medium">${props.nama}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold ${badgeColors[props.jenis]}">
                        ${props.jenis}
                    </span>
                </td>
                <td class="px-4 py-3 text-sm">${props.kecamatan || '-'}</td>
                <td class="px-4 py-3 text-sm">${props.kapasitas || '-'}</td>
                <td class="px-4 py-3 text-sm">
                    <button onclick="editData(${props.id})" class="text-blue-600 hover:text-blue-800 mr-3">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteData(${props.id})" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

// Populate filters
function populateFilters() {
    const kecamatans = [...new Set(allData.map(f => f.properties.kecamatan).filter(k => k))];
    const select = document.getElementById('filter-kecamatan');
    select.innerHTML = '<option value="">Semua</option>' + 
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
    displayTable(filteredData);
    updateDataCount();
}

// Update statistics
function updateStatistics() {
    const stats = {};
    allData.forEach(f => {
        const jenis = f.properties.jenis;
        stats[jenis] = (stats[jenis] || 0) + 1;
    });
    
    document.getElementById('stat-total').textContent = allData.length;
    document.getElementById('stat-masjid').textContent = stats['Masjid'] || 0;
    document.getElementById('stat-gereja').textContent = stats['Gereja'] || 0;
    document.getElementById('stat-lainnya').textContent = 
        (stats['Pura'] || 0) + (stats['Vihara'] || 0) + (stats['Klenteng'] || 0);
}

// Update data count
function updateDataCount() {
    document.getElementById('data-count').textContent = filteredData.length;
}

// Show add modal
function showAddModal(lat = null, lng = null) {
    isEditMode = false;
    currentEditId = null;
    
    const modal = document.getElementById('modal-form');
    modal.innerHTML = createModalHTML(lat, lng);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Show edit modal
function editData(id) {
    const feature = allData.find(f => f.properties.id === id);
    if (!feature) return;
    
    isEditMode = true;
    currentEditId = id;
    const props = feature.properties;
    
    const modal = document.getElementById('modal-form');
    modal.innerHTML = createModalHTML(props.latitude, props.longitude, props);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Create modal HTML
function createModalHTML(lat, lng, data = null) {
    const kecamatanOptions = [
        'Kemiling', 'Teluk Betung Barat', 'Teluk Betung Timur', 'Teluk Betung Selatan',
        'Bumi Waras', 'Panjang', 'Sukabumi', 'Sukarame', 'Tanjung Senang', 'Rajabasa',
        'Langkapura', 'Labuhan Ratu', 'Kedaton', 'Way Halim', 'Kedamaian',
        'Teluk Betung Utara', 'Enggal', 'Tanjung Karang Timur', 'Tanjung Karang Pusat',
        'Tanjung Karang Barat'
    ];
    
    return `
        <div class="bg-white rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto mx-4">
            <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-800 text-white p-4 flex justify-between items-center z-10">
                <h2 class="text-xl font-bold">${isEditMode ? 'Edit' : 'Tambah'} Data Sarana Ibadah</h2>
                <button onclick="closeModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <form id="data-form" class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Sarana Ibadah <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="form-nama" value="${data?.nama || ''}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis <span class="text-red-500">*</span>
                        </label>
                        <select id="form-jenis" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Jenis</option>
                            <option value="Masjid" ${data?.jenis === 'Masjid' ? 'selected' : ''}>Masjid</option>
                            <option value="Gereja" ${data?.jenis === 'Gereja' ? 'selected' : ''}>Gereja</option>
                            <option value="Pura" ${data?.jenis === 'Pura' ? 'selected' : ''}>Pura</option>
                            <option value="Vihara" ${data?.jenis === 'Vihara' ? 'selected' : ''}>Vihara</option>
                            <option value="Klenteng" ${data?.jenis === 'Klenteng' ? 'selected' : ''}>Klenteng</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kecamatan</label>
                        <select id="form-kecamatan"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Kecamatan --</option>
                            ${kecamatanOptions.map(kec => 
                                `<option value="${kec}" ${data?.kecamatan === kec ? 'selected' : ''}>${kec}</option>`
                            ).join('')}
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Latitude <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="form-latitude" value="${lat || data?.latitude || ''}" 
                            step="0.000001" required readonly
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Longitude <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="form-longitude" value="${lng || data?.longitude || ''}" 
                            step="0.000001" required readonly
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                        <textarea id="form-alamat" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">${data?.alamat || ''}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kapasitas (orang)</label>
                        <input type="number" id="form-kapasitas" value="${data?.kapasitas || ''}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tahun Berdiri</label>
                        <input type="number" id="form-tahun" value="${data?.tahun_berdiri || ''}" min="1900" max="2025"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea id="form-keterangan" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">${data?.keterangan || ''}</textarea>
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t">
                    <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg transition">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                    <button type="button" onclick="closeModal()"
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 rounded-lg transition">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                </div>
            </form>
        </div>
    `;
}

// Close modal
function closeModal() {
    const modal = document.getElementById('modal-form');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    
    if (tempMarker) {
        map.removeLayer(tempMarker);
        tempMarker = null;
    }
}

// Save data (Create/Update)
async function saveData(formData) {
    const url = isEditMode ? API_URL + 'update.php' : API_URL + 'create.php';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            closeModal();
            loadData();
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Save error:', error);
        showNotification('Terjadi kesalahan saat menyimpan data', 'error');
    }
}

// Delete data
async function deleteData(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) return;
    
    try {
        const response = await fetch(API_URL + 'delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            loadData();
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showNotification('Terjadi kesalahan saat menghapus data', 'error');
    }
}

// Refresh data
function refreshData() {
    loadData();
    showNotification('Data berhasil direfresh', 'success');
}

// Export data
function exportData() {
    const dataStr = JSON.stringify(allData, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(dataBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `sarana-ibadah-${new Date().toISOString().split('T')[0]}.json`;
    link.click();
    URL.revokeObjectURL(url);
    showNotification('Data berhasil diexport', 'success');
}

// Show notification
function showNotification(message, type = 'info') {
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
    notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>${message}`;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Form submit handler
document.addEventListener('DOMContentLoaded', async function() {
    // Check auth first
    const isAuthenticated = await checkAuth();
    if (!isAuthenticated) return;
    
    // Initialize
    initMap();
    loadData();
    
    // Delegate form submit
    document.addEventListener('submit', function(e) {
        if (e.target.id === 'data-form') {
            e.preventDefault();
            
            const formData = {
                nama: document.getElementById('form-nama').value,
                jenis: document.getElementById('form-jenis').value,
                latitude: parseFloat(document.getElementById('form-latitude').value),
                longitude: parseFloat(document.getElementById('form-longitude').value),
                alamat: document.getElementById('form-alamat').value,
                kecamatan: document.getElementById('form-kecamatan').value,
                kapasitas: parseInt(document.getElementById('form-kapasitas').value) || 0,
                tahun_berdiri: parseInt(document.getElementById('form-tahun').value) || null,
                keterangan: document.getElementById('form-keterangan').value
            };
            
            if (isEditMode) {
                formData.id = currentEditId;
            }
            
            saveData(formData);
        }
    });
});