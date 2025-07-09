-- Insert categories
INSERT INTO categories (name, slug) VALUES
('Makanan Utama', 'makanan-utama'),
('Camilan', 'camilan'),
('Minuman', 'minuman'),
('Hidangan Penutup', 'hidangan-penutup');

-- Insert admin user
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@recipe.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert sample recipes
INSERT INTO recipes (title, description, ingredients, instructions, prep_time, cook_time, servings, difficulty, category_id, image_url, status, created_by) VALUES
('Nasi Goreng Spesial', 'Nasi goreng dengan bumbu rahasia yang lezat', 'Nasi putih 3 piring\nTelur 2 butir\nBawang merah 5 siung\nBawang putih 3 siung\nCabai merah 3 buah\nKecap manis 3 sdm\nGaram secukupnya\nMinyak goreng', '1. Panaskan minyak dalam wajan\n2. Tumis bumbu halus hingga harum\n3. Masukkan telur, orak-arik\n4. Tambahkan nasi, aduk rata\n5. Beri kecap manis dan garam\n6. Aduk hingga merata dan matang', 15, 10, 3, 'Easy', 1, '/images/nasi-goreng.jpg', 'approved', 2),

('Rendang Daging Sapi', 'Rendang daging sapi khas Padang yang empuk dan gurih', 'Daging sapi 1 kg\nSantan kental 1 liter\nSerai 3 batang\nDaun jeruk 10 lembar\nDaun kunyit 5 lembar\nBumbu halus: cabai merah, bawang merah, bawang putih, jahe, kunyit, kemiri', '1. Haluskan semua bumbu\n2. Tumis bumbu halus hingga harum\n3. Masukkan daging, masak hingga berubah warna\n4. Tuang santan, masak dengan api kecil\n5. Tambahkan serai dan daun-daunan\n6. Masak hingga santan mengental dan daging empuk', 30, 180, 6, 'Hard', 1, '/images/rendang.jpg', 'approved', 2),

('Keripik Singkong', 'Keripik singkong renyah dan gurih', 'Singkong 1 kg\nGaram 1 sdt\nMinyak goreng secukupnya\nBumbu tabur sesuai selera', '1. Kupas dan iris tipis singkong\n2. Rendam dalam air garam 30 menit\n3. Tiriskan dan keringkan\n4. Goreng dalam minyak panas hingga kuning keemasan\n5. Tiriskan dan taburi bumbu', 45, 15, 4, 'Medium', 2, '/images/keripik-singkong.jpg', 'approved', 2),

('Brownies Coklat', 'Brownies coklat lembut dan manis', 'Coklat batang 200g\nMentega 150g\nTelur 3 butir\nGula pasir 150g\nTepung terigu 100g\nVanili 1 sdt', '1. Lelehkan coklat dan mentega\n2. Kocok telur dan gula hingga mengembang\n3. Campurkan coklat leleh ke adonan telur\n4. Masukkan tepung dan vanili\n5. Tuang ke loyang, panggang 180°C selama 25 menit', 20, 25, 8, 'Medium', 4, '/images/brownies.jpg', 'approved', 2),

('Es Teh Manis', 'Es teh manis segar untuk cuaca panas', 'Teh celup 2 kantong\nAir panas 500ml\nGula pasir 3 sdm\nEs batu secukupnya\nDaun mint untuk garnish', '1. Seduh teh dengan air panas 5 menit\n2. Tambahkan gula, aduk rata\n3. Biarkan dingin\n4. Tuang ke gelas berisi es batu\n5. Hias dengan daun mint', 5, 0, 2, 'Easy', 3, '/images/es-teh.jpg', 'approved', 2),

('Sate Ayam', 'Sate ayam dengan bumbu kacang yang lezat', 'Daging ayam 500g\nBumbu marinasi: kecap manis, bawang putih, ketumbar\nBumbu kacang: kacang tanah, cabai, gula merah, asam jawa', '1. Potong daging ayam, marinasi 2 jam\n2. Tusuk dengan tusukan sate\n3. Bakar sambil diolesi bumbu\n4. Buat bumbu kacang dengan menghaluskan semua bahan\n5. Sajikan dengan bumbu kacang', 30, 20, 4, 'Medium', 1, '/images/sate-ayam.jpg', 'approved', 3),

('Pisang Goreng Crispy', 'Pisang goreng dengan tepung crispy yang renyah', 'Pisang raja 6 buah\nTepung terigu 200g\nTepung beras 50g\nGaram 1/2 sdt\nAir es 250ml\nMinyak goreng', '1. Campurkan tepung terigu, tepung beras, dan garam\n2. Tambahkan air es sedikit demi sedikit\n3. Celupkan pisang ke adonan\n4. Goreng dalam minyak panas hingga kuning keemasan\n5. Tiriskan dan sajikan hangat', 15, 10, 6, 'Easy', 2, '/images/pisang-goreng.jpg', 'approved', 3),

('Jus Alpukat', 'Jus alpukat segar dan creamy', 'Alpukat matang 2 buah\nSusu kental manis 3 sdm\nAir dingin 200ml\nEs batu secukupnya\nGula pasir 1 sdm (opsional)', '1. Keruk daging alpukat\n2. Blender dengan susu kental manis dan air\n3. Tambahkan gula jika perlu\n4. Tuang ke gelas berisi es batu\n5. Sajikan segera', 10, 0, 2, 'Easy', 3, '/images/jus-alpukat.jpg', 'approved', 3),

('Puding Coklat', 'Puding coklat lembut dan manis', 'Agar-agar coklat 1 bungkus\nSusu cair 500ml\nGula pasir 4 sdm\nCoklat bubuk 2 sdm\nVanili 1 sdt', '1. Rebus susu dengan gula dan coklat bubuk\n2. Masukkan agar-agar, aduk hingga larut\n3. Tambahkan vanili\n4. Tuang ke cetakan\n5. Dinginkan dalam kulkas hingga set', 15, 10, 6, 'Easy', 4, '/images/puding-coklat.jpg', 'approved', 3),

('Gado-Gado', 'Gado-gado dengan sayuran segar dan bumbu kacang', 'Kangkung 100g\nTauge 100g\nKentang 2 buah\nTelur rebus 2 butir\nTahu 4 potong\nTempe 4 potong\nBumbu kacang siap pakai', '1. Rebus semua sayuran hingga matang\n2. Goreng tahu dan tempe\n3. Potong kentang dan telur\n4. Tata semua bahan di piring\n5. Siram dengan bumbu kacang\n6. Taburi kerupuk', 25, 15, 2, 'Easy', 1, '/images/gado-gado.jpg', 'approved', 1);

-- Insert sample ratings
INSERT INTO ratings (recipe_id, user_id, rating) VALUES
(1, 2, 5), (1, 3, 4), (1, 1, 5),
(2, 2, 5), (2, 3, 5), (2, 1, 4),
(3, 2, 4), (3, 3, 3), (3, 1, 4),
(4, 2, 5), (4, 3, 5), (4, 1, 5),
(5, 2, 4), (5, 3, 4), (5, 1, 3),
(6, 2, 5), (6, 3, 4), (6, 1, 5),
(7, 2, 4), (7, 3, 4), (7, 1, 4),
(8, 2, 3), (8, 3, 4), (8, 1, 4),
(9, 2, 5), (9, 3, 5), (9, 1, 4),
(10, 2, 4), (10, 3, 4), (10, 1, 5);

-- Insert sample comments
INSERT INTO comments (recipe_id, user_id, comment) VALUES
(1, 2, 'Resep nasi goreng yang sangat enak! Keluarga saya suka sekali.'),
(1, 3, 'Mudah dibuat dan rasanya autentik. Terima kasih resepnya!'),
(2, 2, 'Rendang terenak yang pernah saya buat. Prosesnya memang lama tapi worth it.'),
(4, 3, 'Brownies yang lembut dan tidak terlalu manis. Perfect!'),
(5, 2, 'Segar banget untuk cuaca panas. Simple tapi enak.'),
(6, 3, 'Sate ayamnya juara! Bumbu kacangnya pas banget.'),
(7, 2, 'Pisang gorengnya crispy di luar, lembut di dalam. Mantap!'),
(8, 3, 'Jus alpukat yang creamy dan segar. Anak-anak suka.'),
(9, 2, 'Puding coklat yang mudah dibuat dan rasanya enak.'),
(10, 3, 'Gado-gado yang segar dan sehat. Cocok untuk diet.');
