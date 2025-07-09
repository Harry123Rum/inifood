-- Membuat tabel untuk data tim
USE recipe_management;

-- Tabel team_members
CREATE TABLE team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert data tim dengan path ke folder images/team/
INSERT INTO team_members (name, role, description, image_url, display_order) VALUES
('Sarah Wijaya', 'Recipe Creator', 'Passionate food enthusiast dengan pengalaman 8 tahun dalam menciptakan resep-resep inovatif. Sarah menggabungkan cita rasa tradisional Indonesia dengan sentuhan modern untuk menghadirkan resep yang unik dan lezat.', 'images/team/sarah-wijaya.jpg', 1),

('Chef Ahmad Rizki', 'Head Chef', 'Chef profesional dengan sertifikasi internasional dan pengalaman 12 tahun di industri kuliner. Ahmad memastikan setiap resep telah diuji dan memenuhi standar kualitas tinggi sebelum dipublikasikan.', 'images/team/ahmad-rizki.jpg', 2),

('Dimas Pratama', 'Web Developer', 'Full-stack developer dengan spesialisasi dalam pengembangan aplikasi web modern. Dimas bertanggung jawab membangun dan memelihara platform IniFood agar selalu optimal dan user-friendly.', 'images/team/dimas-pratama.jpg', 3),

('Maya Sari', 'Content Editor', 'Editor berpengalaman dengan latar belakang jurnalisme kuliner. Maya memastikan setiap konten resep ditulis dengan jelas, menarik, dan mudah dipahami oleh pembaca dari berbagai tingkat keahlian memasak.', 'images/team/maya-sari.jpg', 4),

('Andi Kurniawan', 'Quality Assurance', 'QA specialist yang teliti dalam memastikan kualitas setiap aspek website dan resep. Andi melakukan pengujian menyeluruh untuk menjamin pengalaman pengguna yang optimal dan akurasi informasi resep.', 'images/team/andi-kurniawan.jpg', 5);
