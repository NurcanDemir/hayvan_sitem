/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    // Kök dizindeki PHP ve HTML dosyalarını tarar
    "./*.{php,html}", 
    // 'includes' klasörü içindeki tüm PHP, HTML ve JS dosyalarını tarar
    "./includes/**/*.{php,html,js}",
    // 'admin' klasörü içindeki tüm PHP dosyalarını tarar (çok önemli!)
    "./admin/**/*.php",
    // Eğer 'js' adında bir klasörünüz varsa ve içinde Tailwind sınıfları kullanıyorsanız ekleyin:
    // "./js/**/*.js", 
    // Diğer özel klasörleriniz varsa benzer şekilde ekleyebilirsiniz.
    // ÖNEMLİ: "./**/*.js" gibi genel kalıplardan kaçının
    // Eğer frontend'de JS içinde dinamik Tailwind sınıfları oluşturuyorsanız
    // ve bu sınıfların taranması gerekiyorsa, o JS dosyasının yolunu belirtin.
  ],
  theme: {
    extend: {
      colors: {
        'koyu-pembe': '#C2185B', // Daha belirgin bir pembe tonu
        'acik-pembe': '#F8BBD0', // Daha açık bir pastel pembe
        'toz-pembe': '#F48FB1',  // Biraz daha koyu bir toz pembe
        'soluk-mavi': '#BBDEFB', // Açık pastel mavi
        'acik-yesil': '#A5D6A7', // Açık pastel yeşil
        'koyu-yesil': '#4CAF50', // Daha koyu, canlı bir yeşil
        'bej': '#F5F5DC', // Nötr bej tonu
        'gri-ton': '#E0E0E0', // Açık gri tonu
        'gri-text': '#4B5563', // Metinler için koyu gri
        
      },
    },
  },
  plugins: [],
}