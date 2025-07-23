// Geolocation ve Barınak Bulma JavaScript Kodu
class LocationServices {
    constructor() {
        this.userLocation = null;
        this.shelters = [];
        this.currentLocationAccuracy = null;
    }

    // Haversine formülü ile iki nokta arası mesafe hesaplama (km)
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Dünya'nın yarıçapı (km)
        const dLat = this.toRadians(lat2 - lat1);
        const dLon = this.toRadians(lon2 - lon1);
        
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(this.toRadians(lat1)) * Math.cos(this.toRadians(lat2)) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        const distance = R * c;
        
        return Math.round(distance * 10) / 10; // 1 ondalık basamak
    }

    toRadians(degrees) {
        return degrees * (Math.PI / 180);
    }

    // Kullanıcı konumunu al
    async getUserLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Bu tarayıcı konum hizmetlerini desteklemiyor.'));
                return;
            }

            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000 // 5 dakika cache
            };

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    this.currentLocationAccuracy = position.coords.accuracy;
                    resolve(this.userLocation);
                },
                (error) => {
                    let errorMessage = 'Konum alınamadı: ';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += 'Konum izni reddedildi.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += 'Konum bilgisi mevcut değil.';
                            break;
                        case error.TIMEOUT:
                            errorMessage += 'Konum alma zaman aşımına uğradı.';
                            break;
                        default:
                            errorMessage += 'Bilinmeyen bir hata oluştu.';
                    }
                    reject(new Error(errorMessage));
                },
                options
            );
        });
    }

    // Barınakları getir ve mesafelerine göre sırala
    async loadNearbyShelters() {
        try {
            if (!this.userLocation) {
                await this.getUserLocation();
            }

            const response = await fetch('api/get_nearby_shelters.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    lat: this.userLocation.lat,
                    lng: this.userLocation.lng
                })
            });

            const shelters = await response.json();
            
            if (shelters.error) {
                throw new Error(shelters.error);
            }

            // Mesafeleri hesapla ve sırala
            this.shelters = shelters.map(shelter => {
                shelter.distance = this.calculateDistance(
                    this.userLocation.lat,
                    this.userLocation.lng,
                    shelter.lat,
                    shelter.lng
                );
                return shelter;
            }).sort((a, b) => a.distance - b.distance);

            return this.shelters;

        } catch (error) {
            console.error('Barınaklar yüklenirken hata:', error);
            throw error;
        }
    }

    // Barınakları HTML olarak render et
    renderShelters(shelters, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        if (!shelters || shelters.length === 0) {
            container.innerHTML = `
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-yellow-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        Yakınınızda kayıtlı barınak bulunamadı.
                    </p>
                </div>
            `;
            return;
        }

        const sheltersHTML = shelters.slice(0, 5).map(shelter => `
            <div class="bg-white rounded-lg shadow-md p-4 mb-4 border-l-4 border-emerald-500 hover:shadow-lg transition duration-300">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-bold text-lg text-gray-800 flex-1">${shelter.ad}</h4>
                    <span class="bg-emerald-100 text-emerald-800 text-sm px-2 py-1 rounded-full ml-2">
                        ${shelter.distance} km
                    </span>
                </div>
                
                <p class="text-gray-600 text-sm mb-2">
                    <i class="fas fa-map-marker-alt mr-1 text-emerald-600"></i>
                    ${shelter.adres}
                </p>
                
                ${shelter.telefon ? `
                    <p class="text-gray-600 text-sm mb-2">
                        <i class="fas fa-phone mr-1 text-emerald-600"></i>
                        <a href="tel:${shelter.telefon}" class="hover:text-emerald-700">${shelter.telefon}</a>
                    </p>
                ` : ''}
                
                <div class="flex justify-between items-center mt-3">
                    <div class="text-xs text-gray-500">
                        <span class="mr-3">
                            <i class="fas fa-home mr-1"></i>
                            ${shelter.aktif_hayvan_sayisi}/${shelter.kapasite}
                        </span>
                        <span>
                            <i class="fas fa-clock mr-1"></i>
                            ${shelter.calisma_saatleri || 'Bilgi yok'}
                        </span>
                    </div>
                    <button onclick="showShelterDetails(${shelter.id})" 
                            class="text-emerald-700 hover:text-emerald-800 font-semibold text-sm">
                        Detaylar
                        <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>
        `).join('');

        container.innerHTML = `
            <div class="mb-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-home mr-2 text-emerald-600"></i>
                        Yakındaki Barınaklar
                    </h3>
                    <button onclick="locationServices.refreshLocation()" 
                            class="text-emerald-600 hover:text-emerald-700 text-sm">
                        <i class="fas fa-sync-alt mr-1"></i>Yenile
                    </button>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    <i class="fas fa-location-arrow mr-1"></i>
                    Size en yakın ${shelters.length} barınak bulundu
                </p>
            </div>
            ${sheltersHTML}
            ${shelters.length > 5 ? `
                <div class="text-center mt-4">
                    <button onclick="showAllShelters()" class="text-emerald-700 hover:text-emerald-800 font-semibold">
                        Tüm Barınakları Göster (${shelters.length})
                        <i class="fas fa-chevron-right ml-1"></i>
                    </button>
                </div>
            ` : ''}
        `;
    }

    // Konumu yenile
    async refreshLocation() {
        try {
            const container = document.getElementById('nearby-shelters');
            if (container) {
                container.innerHTML = `
                    <div class="text-center p-8">
                        <i class="fas fa-spinner fa-spin text-3xl text-emerald-600 mb-4"></i>
                        <p class="text-gray-600">Konumunuz alınıyor ve barınaklar aranıyor...</p>
                    </div>
                `;
            }

            this.userLocation = null; // Cache'i temizle
            const shelters = await this.loadNearbyShelters();
            this.renderShelters(shelters, 'nearby-shelters');
            
        } catch (error) {
            this.renderError(error.message, 'nearby-shelters');
        }
    }

    // Hata mesajını render et
    renderError(message, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = `
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-3 mt-1"></i>
                    <div>
                        <p class="text-red-800 font-semibold mb-1">Konum Hatası</p>
                        <p class="text-red-700 text-sm">${message}</p>
                        <button onclick="locationServices.refreshLocation()" 
                                class="mt-2 text-red-700 hover:text-red-800 text-sm underline">
                            Tekrar Dene
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
}

// Global instance
const locationServices = new LocationServices();

// Sayfa yüklendiğinde barınakları getir
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('nearby-shelters')) {
        locationServices.loadNearbyShelters()
            .then(shelters => {
                locationServices.renderShelters(shelters, 'nearby-shelters');
            })
            .catch(error => {
                locationServices.renderError(error.message, 'nearby-shelters');
            });
    }
});

// Debug için log fonksiyonu
function debugLog(message, data = null) {
    console.log('[Location Services]', message, data || '');
}

// Yakındaki barınakları yükle
function loadNearbyShelters() {
    const sheltersContainer = document.getElementById('nearby-shelters');
    debugLog('loadNearbyShelters başlatıldı');
    
    if (!sheltersContainer) {
        debugLog('Barınak container bulunamadı');
        return;
    }
    
    // Konum alma desteğini kontrol et
    if (!navigator.geolocation) {
        debugLog('Geolocation desteklenmiyor');
        sheltersContainer.innerHTML = `
            <div class="text-center p-6">
                <i class="fas fa-exclamation-triangle text-3xl text-red-500 mb-4"></i>
                <h4 class="text-lg font-semibold text-gray-800 mb-2">Konum Desteği Yok</h4>
                <p class="text-gray-600 mb-4">Tarayıcınız konum hizmetlerini desteklemiyor.</p>
                <button onclick="loadAllShelters()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-md">
                    Tüm Barınakları Göster
                </button>
            </div>
        `;
        return;
    }

    // Loading göster
    sheltersContainer.innerHTML = `
        <div class="text-center p-8">
            <i class="fas fa-spinner fa-spin text-3xl text-emerald-600 mb-4"></i>
            <p class="text-gray-600">Konum alınıyor ve barınaklar aranıyor...</p>
        </div>
    `;

    debugLog('Konum isteniyor...');
    
    // Konum izni iste
    navigator.geolocation.getCurrentPosition(
        function(position) {
            const userLat = position.coords.latitude;
            const userLon = position.coords.longitude;
            
            debugLog('Konum alındı:', { lat: userLat, lon: userLon });
            
            // API URL'yi düzelt
            const apiUrl = `api/get-nearby-shelters.php?latitude=${userLat}&longitude=${userLon}`;
            debugLog('API çağrısı yapılıyor:', apiUrl);
            
            // Barınakları getir ve mesafeye göre sırala
            fetch(apiUrl)
                .then(response => {
                    debugLog('API yanıtı alındı, status:', response.status);
                    if (!response.ok) {
                        throw new Error('HTTP hatası: ' + response.status + ' - ' + response.statusText);
                    }
                    return response.text(); // Önce text olarak al
                })
                .then(text => {
                    debugLog('API yanıt metni:', text.substring(0, 200));
                    try {
                        const data = JSON.parse(text);
                        debugLog('JSON parse başarılı:', data);
                        
                        if (data.success && data.shelters && data.shelters.length > 0) {
                            displayShelters(data.shelters, true);
                        } else {
                            debugLog('Barınak bulunamadı veya başarısız yanıt');
                            sheltersContainer.innerHTML = `
                                <div class="text-center p-6">
                                    <i class="fas fa-search text-3xl text-gray-400 mb-4"></i>
                                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Yakında Barınak Bulunamadı</h4>
                                    <p class="text-gray-600 mb-4">50 km yakınında barınak bulunmuyor.</p>
                                    <p class="text-xs text-gray-500 mb-4">Debug: ${data.error || 'Bilinmeyen hata'}</p>
                                    <button onclick="loadAllShelters()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-md">
                                        Tüm Barınakları Göster
                                    </button>
                                </div>
                            `;
                        }
                    } catch (parseError) {
                        debugLog('JSON parse hatası:', parseError);
                        throw new Error('JSON parse hatası: ' + parseError.message + ' - Yanıt: ' + text.substring(0, 100));
                    }
                })
                .catch(error => {
                    debugLog('Fetch hatası:', error);
                    sheltersContainer.innerHTML = `
                        <div class="text-center p-6">
                            <i class="fas fa-exclamation-triangle text-3xl text-red-500 mb-4"></i>
                            <h4 class="text-lg font-semibold text-gray-800 mb-2">Bağlantı Hatası</h4>
                            <p class="text-gray-600 mb-4">Barınak bilgileri yüklenemiyor.</p>
                            <p class="text-xs text-red-500 mb-4">Hata: ${error.message}</p>
                            <button onclick="loadAllShelters()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-md">
                                Tüm Barınakları Göster
                            </button>
                        </div>
                    `;
                });
        },
        function(error) {
            debugLog('Geolocation hatası:', error);
            let errorMessage = '';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = "Konum erişimi reddedildi.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = "Konum bilgisi mevcut değil.";
                    break;
                case error.TIMEOUT:
                    errorMessage = "Konum alınırken zaman aşımı.";
                    break;
                default:
                    errorMessage = "Bilinmeyen konum hatası.";
                    break;
            }
            
            sheltersContainer.innerHTML = `
                <div class="text-center p-6">
                    <i class="fas fa-exclamation-triangle text-3xl text-orange-500 mb-4"></i>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Konum Hatası</h4>
                    <p class="text-gray-600 mb-4">${errorMessage}</p>
                    <button onclick="loadAllShelters()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-md">
                        Tüm Barınakları Göster
                    </button>
                </div>
            `;
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000 // 5 dakika cache
        }
    );
}

// Tüm barınakları yükle (konum olmadan)
function loadAllShelters() {
    const sheltersContainer = document.getElementById('nearby-shelters');
    debugLog('loadAllShelters başlatıldı');
    
    if (!sheltersContainer) {
        debugLog('Barınak container bulunamadı');
        return;
    }
    
    sheltersContainer.innerHTML = `
        <div class="text-center p-8">
            <i class="fas fa-spinner fa-spin text-3xl text-emerald-600 mb-4"></i>
            <p class="text-gray-600">Barınaklar yükleniyor...</p>
        </div>
    `;
    
    const apiUrl = 'api/get-nearby-shelters.php';
    debugLog('Tüm barınaklar için API çağrısı:', apiUrl);
    
    fetch(apiUrl)
        .then(response => {
            debugLog('API yanıtı alındı, status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP hatası: ' + response.status + ' - ' + response.statusText);
            }
            return response.text();
        })
        .then(text => {
            debugLog('API yanıt metni:', text.substring(0, 200));
            try {
                const data = JSON.parse(text);
                debugLog('JSON parse başarılı:', data);
                
                if (data.success && data.shelters && data.shelters.length > 0) {
                    displayShelters(data.shelters, false);
                } else {
                    sheltersContainer.innerHTML = `
                        <div class="text-center p-6">
                            <i class="fas fa-home text-3xl text-gray-400 mb-4"></i>
                            <h4 class="text-lg font-semibold text-gray-800 mb-2">Barınak Bulunamadı</h4>
                            <p class="text-gray-600 mb-2">Henüz kayıtlı barınak bulunmuyor.</p>
                            <p class="text-xs text-gray-500">Debug: ${data.error || 'Veri yok'}</p>
                        </div>
                    `;
                }
            } catch (parseError) {
                debugLog('JSON parse hatası:', parseError);
                throw new Error('JSON parse hatası: ' + parseError.message);
            }
        })
        .catch(error => {
            debugLog('Fetch hatası:', error);
            sheltersContainer.innerHTML = `
                <div class="text-center p-6">
                    <i class="fas fa-exclamation-triangle text-3xl text-red-500 mb-4"></i>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Bağlantı Hatası</h4>
                    <p class="text-gray-600 mb-4">Barınak bilgileri yüklenemiyor.</p>
                    <p class="text-xs text-red-500">Hata: ${error.message}</p>
                </div>
            `;
        });
}

// Güncel etkinlikleri yükle
function loadCurrentEvents() {
    const eventsContainer = document.getElementById('current-events');
    debugLog('loadCurrentEvents başlatıldı');
    
    if (!eventsContainer) {
        debugLog('Etkinlik container bulunamadı');
        return;
    }
    
    // Loading göster
    eventsContainer.innerHTML = `
        <div class="text-center p-8">
            <i class="fas fa-spinner fa-spin text-3xl text-amber-600 mb-4"></i>
            <p class="text-gray-600">Etkinlikler yükleniyor...</p>
        </div>
    `;
    
    const apiUrl = 'api/get-current-events.php';
    debugLog('Etkinlikler için API çağrısı:', apiUrl);
    
    fetch(apiUrl)
        .then(response => {
            debugLog('Etkinlik API yanıtı alındı, status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP hatası: ' + response.status + ' - ' + response.statusText);
            }
            return response.text();
        })
        .then(text => {
            debugLog('Etkinlik API yanıt metni:', text.substring(0, 200));
            try {
                const data = JSON.parse(text);
                debugLog('Etkinlik JSON parse başarılı:', data);
                
                if (data.success && data.events && data.events.length > 0) {
                    displayEvents(data.events);
                } else {
                    eventsContainer.innerHTML = `
                        <div class="text-center p-8">
                            <i class="fas fa-calendar-times text-3xl text-gray-400 mb-4"></i>
                            <h4 class="text-lg font-semibold text-gray-800 mb-2">Etkinlik Bulunamadı</h4>
                            <p class="text-gray-600 mb-2">Şu anda yaklaşan etkinlik bulunmuyor.</p>
                            <p class="text-xs text-gray-500">Debug: ${data.error || 'Veri yok'}</p>
                        </div>
                    `;
                }
            } catch (parseError) {
                debugLog('Etkinlik JSON parse hatası:', parseError);
                throw new Error('JSON parse hatası: ' + parseError.message);
            }
        })
        .catch(error => {
            debugLog('Etkinlik fetch hatası:', error);
            eventsContainer.innerHTML = `
                <div class="text-center p-8">
                    <i class="fas fa-exclamation-triangle text-3xl text-red-500 mb-4"></i>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Bağlantı Hatası</h4>
                    <p class="text-gray-600 mb-4">Etkinlik bilgileri yüklenemiyor.</p>
                    <p class="text-xs text-red-500">Hata: ${error.message}</p>
                </div>
            `;
        });
}

// Barınakları görüntüle
function displayShelters(shelters, showDistance = false) {
    const sheltersContainer = document.getElementById('nearby-shelters');
    debugLog('displayShelters çağrıldı:', { count: shelters.length, showDistance });
    
    let html = `
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-home mr-2 text-emerald-600"></i>
                ${showDistance ? 'Yakındaki' : 'Hayvan'} Barınakları
            </h3>
            <span class="text-sm text-gray-500">${shelters.length} barınak</span>
        </div>
        <div class="space-y-4 max-h-96 overflow-y-auto">
    `;
    
    shelters.forEach(shelter => {
        const distanceText = showDistance && shelter.distance 
            ? `<span class="text-emerald-600 font-semibold">${shelter.distance} km</span>` 
            : '';
            
        html += `
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-300">
                <div class="flex items-start justify-between mb-2">
                    <h4 class="font-semibold text-gray-800 text-lg">${shelter.ad || 'İsimsiz Barınak'}</h4>
                    ${distanceText}
                </div>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-start">
                        <i class="fas fa-map-marker-alt mt-1 mr-2 text-gray-400"></i>
                        <span>${shelter.adres || 'Adres bilgisi yok'}</span>
                    </div>
                    ${shelter.telefon ? `
                        <div class="flex items-center">
                            <i class="fas fa-phone mr-2 text-gray-400"></i>
                            <a href="tel:${shelter.telefon}" class="text-amber-600 hover:text-amber-700">${shelter.telefon}</a>
                        </div>
                    ` : ''}
                    ${shelter.email ? `
                        <div class="flex items-center">
                            <i class="fas fa-envelope mr-2 text-gray-400"></i>
                            <a href="mailto:${shelter.email}" class="text-amber-600 hover:text-amber-700">${shelter.email}</a>
                        </div>
                    ` : ''}
                    ${shelter.website ? `
                        <div class="flex items-center">
                            <i class="fas fa-globe mr-2 text-gray-400"></i>
                            <a href="${shelter.website}" target="_blank" class="text-amber-600 hover:text-amber-700">Web Sitesi</a>
                        </div>
                    ` : ''}
                </div>
                <div class="mt-3 flex gap-2">
                    ${shelter.latitude && shelter.longitude ? `
                        <button onclick="openMaps(${shelter.latitude}, ${shelter.longitude}, '${shelter.ad}')" 
                                class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-2 rounded-md transition duration-300">
                            <i class="fas fa-directions mr-1"></i>Yol Tarifi
                        </button>
                    ` : ''}
                    ${shelter.telefon ? `
                        <a href="tel:${shelter.telefon}" 
                           class="flex-1 bg-amber-600 hover:bg-amber-700 text-white text-xs px-3 py-2 rounded-md text-center transition duration-300">
                            <i class="fas fa-phone mr-1"></i>Ara
                        </a>
                    ` : ''}
                </div>
            </div>
        `;
    });
    
    html += `
        </div>
        <div class="mt-4 text-center">
            <a href="barinaklar.php" class="text-amber-600 hover:text-amber-700 font-semibold">
                Tüm Barınakları Gör <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    `;
    
    sheltersContainer.innerHTML = html;
}

// Etkinlikleri görüntüle
function displayEvents(events) {
    const eventsContainer = document.getElementById('current-events');
    debugLog('displayEvents çağrıldı:', { count: events.length });
    
    let html = '<div class="space-y-6">';
    
    events.forEach(event => {
        const eventDate = new Date(event.etkinlik_tarihi);
        const today = new Date();
        const isToday = eventDate.toDateString() === today.toDateString();
        const isPast = eventDate < today;
        
        // Kategori renkleri
        const categoryColors = {
            'sahiplendirme': 'bg-pink-100 text-pink-700 border-pink-300',
            'saglik': 'bg-green-100 text-green-700 border-green-300',
            'egitim': 'bg-blue-100 text-blue-700 border-blue-300',
            'bagis': 'bg-orange-100 text-orange-700 border-orange-300',
            'diger': 'bg-gray-100 text-gray-700 border-gray-300'
        };
        
        const categoryColor = categoryColors[event.kategori] || categoryColors['diger'];
        
        html += `
            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition duration-300 ${isPast ? 'opacity-75' : ''}">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold border ${categoryColor}">
                                ${getCategoryName(event.kategori)}
                            </span>
                            ${isToday ? '<span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">BUGÜN</span>' : ''}
                            ${isPast ? '<span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">GEÇMİŞ</span>' : ''}
                        </div>
                        <h4 class="text-xl font-bold text-gray-800 mb-2">${event.baslik || 'İsimsiz Etkinlik'}</h4>
                        <p class="text-gray-600 mb-3 line-clamp-2">${event.aciklama || 'Açıklama yok'}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt mr-2 text-amber-600"></i>
                        <span>${formatDate(event.etkinlik_tarihi)}</span>
                    </div>
                    ${event.etkinlik_saati ? `
                        <div class="flex items-center">
                            <i class="fas fa-clock mr-2 text-amber-600"></i>
                            <span>${event.etkinlik_saati}</span>
                        </div>
                    ` : ''}
                    ${event.adres ? `
                        <div class="flex items-center md:col-span-2">
                            <i class="fas fa-map-marker-alt mr-2 text-amber-600"></i>
                            <span>${event.adres}</span>
                        </div>
                    ` : ''}
                </div>
                
                <div class="flex justify-between items-center">
                    <button onclick="shareEvent('${event.baslik}', '${event.etkinlik_tarihi}', '${event.adres || ''}')"
                            class="text-amber-600 hover:text-amber-700 font-semibold">
                        <i class="fas fa-share-alt mr-1"></i>Paylaş
                    </button>
                    <a href="etkinlik-detay.php?id=${event.id}" 
                       class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-md transition duration-300">
                        Detaylar <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    eventsContainer.innerHTML = html;
}

// Haritalar uygulamasında aç
function openMaps(lat, lon, name) {
    const encodedName = encodeURIComponent(name);
    const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${lat},${lon}&query_place_id=${encodedName}`;
    window.open(mapsUrl, '_blank');
}

// Kategori adlarını çevir
function getCategoryName(category) {
    const names = {
        'sahiplendirme': 'Sahiplendirme',
        'saglik': 'Sağlık',
        'egitim': 'Eğitim',
        'bagis': 'Bağış',
        'diger': 'Diğer'
    };
    return names[category] || 'Diğer';
}

// Tarih formatla
function formatDate(dateString) {
    const date = new Date(dateString);
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    if (date.toDateString() === today.toDateString()) {
        return 'Bugün';
    } else if (date.toDateString() === tomorrow.toDateString()) {
        return 'Yarın';
    } else {
        return date.toLocaleDateString('tr-TR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }
}

// Etkinlik paylaş
function shareEvent(title, date, address) {
    if (navigator.share) {
        navigator.share({
            title: title,
            text: `${title} - ${formatDate(date)}${address ? ` - ${address}` : ''}`,
            url: window.location.href
        });
    } else {
        // Fallback: clipboard'a kopyala
        const text = `${title} - ${formatDate(date)}${address ? ` - ${address}` : ''}\n${window.location.href}`;
        navigator.clipboard.writeText(text).then(() => {
            alert('Etkinlik bilgisi panoya kopyalandı!');
        }).catch(() => {
            alert('Panoya kopyalanamadı');
        });
    }
}

// Sayfa yüklendiğinde çalıştır
document.addEventListener('DOMContentLoaded', function() {
    debugLog('Sayfa yüklendi, işlemler başlatılıyor...');
    
    // Barınakları yükle
    if (document.getElementById('nearby-shelters')) {
        debugLog('Barınak container bulundu, yükleniyor...');
        loadNearbyShelters();
    } else {
        debugLog('Barınak container bulunamadı');
    }
    
    // Etkinlikleri yükle
    if (document.getElementById('current-events')) {
        debugLog('Etkinlik container bulundu, yükleniyor...');
        loadCurrentEvents();
    } else {
        debugLog('Etkinlik container bulunamadı');
    }
});
