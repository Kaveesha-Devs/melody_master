-- Melody Masters Instrument Shop Database
CREATE DATABASE IF NOT EXISTS melody_masters;
USE melody_masters;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('customer','staff','admin') DEFAULT 'customer',
    address TEXT,
    city VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50) DEFAULT 'Sri Lanka',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    parent_id INT DEFAULT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    brand VARCHAR(100),
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    stock_quantity INT DEFAULT 0,
    product_type ENUM('physical','digital') DEFAULT 'physical',
    specifications TEXT,
    image VARCHAR(255),
    featured TINYINT(1) DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS digital_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT,
    download_limit INT DEFAULT 5,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    shipping_name VARCHAR(100),
    shipping_address TEXT,
    shipping_city VARCHAR(50),
    shipping_postal VARCHAR(20),
    shipping_country VARCHAR(50),
    payment_method VARCHAR(50) DEFAULT 'cod',
    payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    tracking_number VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS digital_downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    download_count INT DEFAULT 0,
    max_downloads INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(200),
    comment TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    UNIQUE KEY unique_review (product_id, user_id, order_id)
);

CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- Sample Data
-- password for all users: password (hashed)
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@melodymaster.com', '$2y$10$TKh8H1.PfunDArgELX58uuAqy4dHEgS3WjPJBEAMWt12YSbj3NU7a', 'Admin User', 'admin'),
('staff1', 'staff@melodymaster.com', '$2y$10$TKh8H1.PfunDArgELX58uuAqy4dHEgS3WjPJBEAMWt12YSbj3NU7a', 'Staff Member', 'staff'),
('john_doe', 'john@example.com', '$2y$10$TKh8H1.PfunDArgELX58uuAqy4dHEgS3WjPJBEAMWt12YSbj3NU7a', 'John Doe', 'customer');

INSERT INTO categories (name, slug, description, parent_id) VALUES
('Guitars', 'guitars', 'Acoustic, Electric, and Bass Guitars', NULL),
('Keyboards', 'keyboards', 'Digital Pianos and Synthesizers', NULL),
('Drums & Percussion', 'drums-percussion', 'Drum Kits and Percussion Instruments', NULL),
('Wind Instruments', 'wind-instruments', 'Flutes, Saxophones, Trumpets and more', NULL),
('String Instruments', 'string-instruments', 'Violins, Cellos, Ukuleles and more', NULL),
('Accessories', 'accessories', 'Picks, Straps, Cables and Maintenance', NULL),
('Digital Sheet Music', 'digital-sheet-music', 'Downloadable Sheet Music and Scores', NULL),
('Acoustic Guitars', 'acoustic-guitars', 'Classical and Steel-string Acoustic Guitars', 1),
('Electric Guitars', 'electric-guitars', 'Electric Guitars for all styles', 1),
('Bass Guitars', 'bass-guitars', 'Electric and Acoustic Bass Guitars', 1),
('Digital Pianos', 'digital-pianos', 'Weighted keyboard digital pianos', 2),
('Synthesizers', 'synthesizers', 'Analog and Digital Synthesizers', 2),
('Drum Kits', 'drum-kits', 'Acoustic and Electronic Drum Kits', 3),
('Flutes', 'flutes', 'Concert and Student Flutes', 4),
('Saxophones', 'saxophones', 'Alto, Tenor and Soprano Saxophones', 4);

INSERT INTO products (category_id, name, slug, description, brand, price, sale_price, stock_quantity, product_type, specifications, image, featured) VALUES
(8, 'Yamaha FG800 Acoustic Guitar', 'yamaha-fg800', 'The FG800 is a solid top acoustic guitar perfect for beginners and intermediate players. Features a solid Sitka spruce top with nato back and sides for rich, full sound.', 'Yamaha', 299.99, 249.99, 15, 'physical', '{"Top":"Solid Sitka Spruce","Back/Sides":"Nato","Neck":"NATO","Frets":"20","Scale":"648mm"}', 'guitar1.jpg', 1),
(9, 'Fender Stratocaster Electric Guitar', 'fender-stratocaster', 'The iconic Stratocaster electric guitar with tremolo system. Perfect for rock, blues, and country players.', 'Fender', 699.99, NULL, 8, 'physical', '{"Body":"Alder","Neck":"Maple","Frets":"21","Pickups":"3x Single Coil","Bridge":"Vintage Tremolo"}', 'guitar2.jpg', 1),
(10, 'Squier Affinity Bass Guitar', 'squier-affinity-bass', 'Great entry-level bass guitar with powerful humbucking pickup. Ideal for beginners.', 'Squier', 199.99, 179.99, 12, 'physical', '{"Body":"Alder","Neck":"Maple","Frets":"20","Pickup":"Single Humbucking"}', 'bass1.jpg', 0),
(11, 'Yamaha P-45 Digital Piano', 'yamaha-p45', 'Compact and affordable digital piano with 88 weighted keys and authentic piano sound.', 'Yamaha', 499.99, NULL, 6, 'physical', '{"Keys":"88 Weighted","Polyphony":"64","Sounds":"10","MIDI":"USB"}', 'piano1.jpg', 1),
(12, 'Roland JUNO-DS61 Synthesizer', 'roland-juno-ds61', 'Versatile performance synthesizer with 61 keys, ZEN-Core sound engine and battery power support.', 'Roland', 899.99, 799.99, 4, 'physical', '{"Keys":"61","Sounds":"ZEN-Core","Effects":"MFX+TFX","Battery":"8xAA"}', 'synth1.jpg', 1),
(13, 'Pearl Export Drum Kit', 'pearl-export-drum-kit', 'Complete 5-piece drum kit perfect for practice and gigging. Includes hardware and cymbals.', 'Pearl', 799.99, NULL, 3, 'physical', '{"Pieces":"5","Sizes":"22/10/12/16 + 14 Snare","Hardware":"Full Hardware Pack","Cymbals":"Hi-hat + Crash + Ride"}', 'drums1.jpg', 1),
(14, 'Yamaha YFL-222 Student Flute', 'yamaha-yfl-222', 'Excellent student model flute with silver-plated body. Perfect for beginners and school bands.', 'Yamaha', 249.99, NULL, 10, 'physical', '{"Material":"Silver-plated Nickel Silver","Keys":"Closed Hole","Foot":"C Foot Joint","Case":"Included"}', 'flute1.jpg', 0),
(15, 'Alto Saxophone Jupiter JAS500', 'jupiter-jas500', 'Professional-grade alto saxophone with yellow brass body and high F# key.', 'Jupiter', 999.99, 899.99, 5, 'physical', '{"Type":"Alto","Material":"Yellow Brass","Keys":"High F#","Finish":"Gold Lacquer"}', 'sax1.jpg', 0),
(6, 'Guitar Pick Set 50 pcs', 'guitar-pick-set-50', 'Variety pack of 50 guitar picks in different thicknesses: thin, medium, heavy.', 'Generic', 9.99, NULL, 100, 'physical', '{"Count":"50 pieces","Thicknesses":"Thin, Medium, Heavy","Material":"Celluloid"}', 'picks1.jpg', 0),
(6, 'Premium Leather Guitar Strap', 'guitar-strap-leather', 'Premium genuine leather guitar strap with adjustable length. Compatible with all guitars.', 'Generic', 29.99, NULL, 50, 'physical', '{"Material":"Genuine Leather","Length":"Adjustable 100-155cm","Color":"Brown"}', 'strap1.jpg', 0),
(7, 'Beginner Guitar Chords Sheet Music', 'beginner-guitar-chords', 'Complete digital guide with 50+ essential guitar chord diagrams for beginners. Instant download PDF.', 'MelodyMasters', 4.99, NULL, 999, 'digital', '{"Format":"PDF","Pages":"45","Level":"Beginner","Genre":"All Genres"}', 'sheet1.jpg', 0),
(7, 'Classical Piano Pieces Vol.1', 'classical-piano-pieces-vol1', 'Collection of 20 classical piano pieces including works by Bach, Mozart and Beethoven.', 'MelodyMasters', 9.99, NULL, 999, 'digital', '{"Format":"PDF","Pieces":"20","Level":"Intermediate","Composers":"Bach, Mozart, Beethoven"}', 'sheet2.jpg', 0),
(9, 'Gibson Les Paul Standard', 'gibson-les-paul-standard', 'The legendary Les Paul Standard with mahogany body and maple top. Produces that iconic thick, warm tone.', 'Gibson', 2499.99, 2199.99, 2, 'physical', '{"Body":"Mahogany + Maple Top","Neck":"Mahogany","Pickups":"2x Humbucker","Weight Relief":"Yes"}', 'guitar3.jpg', 1),
(11, 'Casio CT-S300 Keyboard', 'casio-cts300', 'Portable 61-key keyboard perfect for beginners. Comes with 300 tones and built-in rhythms.', 'Casio', 79.99, NULL, 20, 'physical', '{"Keys":"61","Tones":"300","Rhythms":"77","Power":"Battery or Adapter"}', 'keyboard1.jpg', 0);

INSERT INTO digital_products (product_id, file_path, file_name, file_size, download_limit) VALUES
(11, 'downloads/beginner_guitar_chords.pdf', 'Beginner_Guitar_Chords.pdf', 2048000, 5),
(12, 'downloads/classical_piano_vol1.pdf', 'Classical_Piano_Pieces_Vol1.pdf', 5120000, 5);
