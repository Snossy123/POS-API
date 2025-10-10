-- Create database with UTF-8 support
DROP DATABASE IF EXISTS pos_system;
CREATE DATABASE pos_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pos_system;

-- ============================
-- Categories table
-- ============================
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  color VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================
-- Products table
-- ============================
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  hasSizes BOOLEAN DEFAULT FALSE,
  price DECIMAL(10,2) DEFAULT 0.00,
  s_price DECIMAL(10,2) DEFAULT 0.00,
  m_price DECIMAL(10,2) DEFAULT 0.00,
  l_price DECIMAL(10,2) DEFAULT 0.00,
  stock INT DEFAULT 0,
  barcode VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci UNIQUE,
  category_id INT,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================
-- Purchase invoices table
-- ============================
CREATE TABLE purchase_invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_number VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
  supplier VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  date DATE NOT NULL,
  time TIME NOT NULL,
  total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================
-- Invoice items table
-- ============================
CREATE TABLE invoice_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  product_id INT,
  product_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  barcode VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  quantity INT NOT NULL DEFAULT 1,
  purchase_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  sale_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  category_id INT,
  FOREIGN KEY (invoice_id) REFERENCES purchase_invoices(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================
-- Sales invoices table
-- ============================
CREATE TABLE sales_invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_number VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci UNIQUE,
  date DATE NOT NULL,
  time TIME NOT NULL,
  cashier VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================
-- Sales invoice items table
-- ============================
CREATE TABLE sales_invoice_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  product_id INT,
  product_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  quantity INT NOT NULL DEFAULT 1,
  barcode VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  FOREIGN KEY (invoice_id) REFERENCES sales_invoices(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ============================
-- Sample Data Insertion
-- ============================
-- Insert some sample data to test Arabic support
-- ============================
-- ======================================
-- 🏷️ Categories
-- ======================================
INSERT INTO categories (id, name, description, color)
VALUES
(1, 'عالم المانجو', 'عصائر المانجو بنكهات متعددة', '#FFA500'),
(2, 'مشروبات الطاقة', 'مشروبات منشطة ومنعشة', '#FF0000'),
(3, 'الأفوكادو', 'عصائر الأفوكادو بنكهات متنوعة', '#228B22'),
(4, 'العصائر', 'عصائر فواكه طبيعية ومنعشة', '#FFA07A');

-- ======================================
-- 🧃 Products
-- ======================================

-- 🌴 عالم المانجو (category_id = 1)
INSERT INTO products (name, price, barcode, category_id) VALUES
('مانجو عادي', 35.00, 'MNG001', 1),
('مانجو ممتاز', 40.00, 'MNG002', 1),
('مانجو كيوي', 50.00, 'MNG003', 1),
('مانجو فراولة', 50.00, 'MNG004', 1),
('مانجو أناناس', 50.00, 'MNG005', 1),
('مانجو مانجو', 50.00, 'MNG006', 1);

-- ⚡ مشروبات الطاقة (category_id = 2)
INSERT INTO products (name, price, barcode, category_id) VALUES
('ريد بول', 60.00, 'EN001', 2),
('بريل', 60.00, 'EN002', 2),
('بيبسي', 60.00, 'EN003', 2),
('فيروز', 60.00, 'EN004', 2),
('ميراندا', 70.00, 'EN005', 2),
('حمضيات طاقة', 60.00, 'EN006', 2);

-- 🥑 الأفوكادو (category_id = 3)
INSERT INTO products (name, price, barcode, category_id) VALUES
('أفوكادو عادي', 65.00, 'AV001', 3),
('أفوكادو عسل', 70.00, 'AV002', 3),
('أفوكادو فواكه', 80.00, 'AV003', 3),
('أفوكادو كاجو', 90.00, 'AV004', 3),
('سوبر أفوكادو', 100.00, 'AV005', 3),
('أفوكادو مانجو', 60.00, 'AV006', 3);

-- 🍊 العصائر (category_id = 4)
INSERT INTO products (name, price, barcode, category_id) VALUES
('برتقال', 50.00, 'JU001', 4),
('أناناس', 50.00, 'JU002', 4),
('تفاح', 50.00, 'JU003', 4),
('جوافة', 50.00, 'JU004', 4),
('مانجو', 50.00, 'JU005', 4),
('فيمتو', 50.00, 'JU006', 4),
('كوكتيل', 70.00, 'JU007', 4),
('كيوي', 50.00, 'JU008', 4),
('فراولة', 70.00, 'JU009', 4),
('رمان', 50.00, 'JU010', 4),
('تمر هندي', 70.00, 'JU011', 4),
('ليمون', 50.00, 'JU012', 4);
