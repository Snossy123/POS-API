-- Create database with UTF-8 support
DROP DATABASE IF EXISTS pos_system;
CREATE DATABASE pos_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pos_system;


-- ============================
-- Employees table
-- ============================
CREATE TABLE employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  email VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
  password VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  role VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  phone VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  salary DECIMAL(10, 2) DEFAULT 0.00,
  hiring_date DATE NULL,
  active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  image VARCHAR(255) NULL,
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
  employee_id INT,
  total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  kitchen_note TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL,
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
-- 🥤 الفئة 1: عصائر طازجة
-- ======================================
INSERT INTO categories (id, name, color) VALUES
(1, 'عصائر طازجة', '#FFA500');

INSERT INTO products (name, hasSizes, s_price, m_price, l_price, barcode, category_id) VALUES
('مانجو', TRUE, 35.00, 45.00, 55.00, '1', 1),
('برتقال', TRUE, 30.00, 40.00, 50.00, '2', 1),
('ليمون', TRUE, 30.00, 40.00, 50.00, '3', 1),
('تفاح', TRUE, 35.00, 45.00, 55.00, '4', 1),
('جوافة', TRUE, 35.00, 45.00, 55.00, '5', 1),
('فراولة', TRUE, 35.00, 45.00, 55.00, '6', 1),
('رمان', TRUE, 35.00, 45.00, 55.00, '7', 1),
('كيوي', TRUE, 35.00, 45.00, 55.00, '8', 1),
('كوكتيل', TRUE, 40.00, 50.00, 60.00, '9', 1),
('ليمون نعناع', TRUE, 35.00, 45.00, 55.00, '10', 1),
('تفاح أحمر', TRUE, 35.00, 45.00, 55.00, '11', 1),
('تفاح أخضر', TRUE, 35.00, 45.00, 55.00, '12', 1),
('حليب بالموز', TRUE, 40.00, 50.00, 60.00, '13', 1),
('حليب بالتفاح', TRUE, 40.00, 50.00, 60.00, '14', 1),
('حليب بالعسل', TRUE, 40.00, 50.00, 60.00, '15', 1),
('حليب بالرمان', TRUE, 40.00, 50.00, 60.00, '16', 1);

-- ======================================
-- 🌾 الفئة 2: عالم القصب
-- ======================================
INSERT INTO categories (id, name, color) VALUES
(2, 'عالم القصب', '#32CD32');

INSERT INTO products (name, hasSizes, s_price, m_price, l_price, barcode, category_id) VALUES
('قصب سادة', TRUE, 15.00, 20.00, 25.00, '17', 2),
('قصب بالبرتقال', TRUE, 20.00, 25.00, 30.00, '18', 2),
('قصب بالليمون', TRUE, 20.00, 25.00, 30.00, '19', 2),
('قصب بالرمان', TRUE, 20.00, 25.00, 30.00, '20', 2),
('قصب بالفراولة', TRUE, 25.00, 30.00, 35.00, '21', 2),
('قصب بالكيوي', TRUE, 25.00, 30.00, 35.00, '22', 2);

-- ======================================
-- 🧃 الفئة 3: عصائر شرقية
-- ======================================
INSERT INTO categories (id, name, color) VALUES
(3, 'عصائر شرقية', '#DC143C');

INSERT INTO products (name, hasSizes, s_price, m_price, l_price, barcode, category_id) VALUES
('تمر هندي', TRUE, 15.00, 20.00, 25.00, '23', 3),
('كركديه', TRUE, 15.00, 20.00, 25.00, '24', 3),
('سوبيا', TRUE, 20.00, 25.00, 30.00, '25', 3),
('خروب', TRUE, 20.00, 25.00, 30.00, '26', 3),
('لبن بالبلح', TRUE, 25.00, 30.00, 35.00, '27', 3),
('لبن بالعسل', TRUE, 25.00, 30.00, 35.00, '28', 3);

-- ======================================
-- 🍨 الفئة 4: الأيس كريم
-- ======================================
INSERT INTO categories (id, name, color) VALUES
(4, 'الأيس كريم', '#FF69B4');

INSERT INTO products (name, price, barcode, category_id) VALUES
('كورة', 20.00, '29', 4),
('كورتين', 30.00, '30', 4),
('ثلاث كور', 40.00, '31', 4);
-- ======================================
-- 🥤 الفئة 5: الميلك شيك
-- ======================================
INSERT INTO categories (id, name, color) VALUES
(5, 'الميلك شيك', '#FFB6C1');

INSERT INTO products (name, hasSizes, s_price, m_price, l_price, barcode, category_id) VALUES
('شيك فراولة', TRUE, 45.00, 50.00, 60.00, '32', 5),
('شيك موز', TRUE, 45.00, 50.00, 60.00, '33', 5),
('شيك مانجو', TRUE, 45.00, 50.00, 60.00, '34', 5),
('شيك كيوي', TRUE, 45.00, 50.00, 60.00, '35', 5),
('شيك شوكولاتة', TRUE, 45.00, 50.00, 60.00, '36', 5);

-- ======================================
-- 🍧 الفئة 6: الآيسكريم فلو
-- ======================================
INSERT INTO categories (id, name, color) VALUES
(6, 'الآيسكريم فلو', '#87CEFA');

INSERT INTO products (name, price, barcode, category_id) VALUES
('كولا فلو', 45.00, '37', 6),
('بيبسي فلو', 45.00, '38', 6),
('فيمتو فلو', 45.00, '39', 6),
('ريد بول فلو', 50.00, '40', 6),
('بلوبيري فلو', 50.00, '41', 6);

-- ======================================
-- 🥭 الفئة 7: عالم المانجو
-- ======================================
INSERT INTO categories (id, name, color) VALUES
(7, 'عالم المانجو', '#FFA500');

INSERT INTO products (name, hasSizes, s_price, m_price, l_price, barcode, category_id) VALUES
('مانجو عادي', TRUE, 35.00, 40.00, 50.00, '42', 7),
('مانجو ممتاز', TRUE, 40.00, 45.00, 55.00, '43', 7),
('مانجو كيوي', TRUE, 45.00, 50.00, 60.00, '44', 7),
('مانجو فراولة', TRUE, 45.00, 50.00, 60.00, '45', 7),
('مانجو أناناس', TRUE, 45.00, 50.00, 60.00, '46', 7),
('مانجو مانجو', TRUE, 50.00, 55.00, 65.00, '47', 7);

-- ======================================
-- ⚡ الفئة 8: مشروبات الطاقة
-- ======================================
INSERT INTO categories (id, name, color) VALUES
(8, 'مشروبات الطاقة', '#FF0000');

INSERT INTO products (name, price, barcode, category_id) VALUES
('ريد بول', 60.00, '48', 8),
('بريل', 60.00, '49', 8),
('بيبسي', 60.00, '50', 8),
('فيروز', 60.00, '51', 8),
('ميراندا', 70.00, '52', 8),
('حمضيات طاقة', 60.00, '53', 8);

-- ======================================
-- 🧇 الفئة 9: الكريب والوافل
-- ======================================
INSERT INTO categories (id, name, color) VALUES
(9, 'الكريب والوافل', '#D2691E');

INSERT INTO products (name, price, barcode, category_id) VALUES
('وافل سادة', 35.00, '54', 9),
('وافل نوتيلا', 40.00, '55', 9),
('وافل نوتيلا موز', 45.00, '56', 9),
('كريب سادة', 35.00, '57', 9),
('كريب نوتيلا', 40.00, '58', 9),
('كريب نوتيلا موز', 45.00, '59', 9);

-- ======================================
-- 🥤 الفئة 10: Soda
-- ======================================
INSERT INTO categories (id, name, color) VALUES
(10, 'Soda', '#00CED1');

INSERT INTO products (name, price, barcode, category_id) VALUES
('سودا ليمون نعناع', 40.00, '60', 10),
('سودا كيوي', 40.00, '61', 10),
('سودا بلوبيري', 40.00, '62', 10),
('سودا فيمتو', 40.00, '63', 10),
('سودا أناناس', 40.00, '64', 10),
('سودا ريد بول', 45.00, '65', 10);
-- ======================================
-- 🍮 الفئة 11: كشري الحلو
-- ======================================
INSERT INTO categories (id, name, color) VALUES
(11, 'كشري الحلو', '#DAA520');

INSERT INTO products (name, price, barcode, category_id) VALUES
('كشري حلو صغير', 25.00, '66', 11),
('كشري حلو وسط', 35.00, '67', 11),
('كشري حلو كبير', 45.00, '68', 11),
('كشري حلو ملكي', 55.00, '69', 11),
('كشري حلو كراميل', 50.00, '70', 11),
('كشري حلو شيكولاتة', 50.00, '71', 11);

-- ======================================
-- 🍹 الفئة 12: الفيمتو والمخلوطات
-- ======================================
INSERT INTO categories (id, name, color) VALUES
(12, 'الفيمتو والمخلوطات', '#8B008B');

INSERT INTO products (name, hasSizes, s_price, m_price, l_price, barcode, category_id) VALUES
('فيمتو', TRUE, 30.00, 40.00, 50.00, '72', 12),
('فيمتو كوكتيل', TRUE, 35.00, 45.00, 55.00, '73', 12),
('فيمتو أناناس', TRUE, 35.00, 45.00, 55.00, '74', 12),
('فيمتو كيوي', TRUE, 35.00, 45.00, 55.00, '75', 12),
('مخلوط مانجو فراولة', TRUE, 40.00, 50.00, 60.00, '76', 12),
('مخلوط مانجو رمان', TRUE, 40.00, 50.00, 60.00, '77', 12),
('مخلوط مانجو كيوي', TRUE, 40.00, 50.00, 60.00, '78', 12);

-- ======================================
-- ☕ الفئة 13: المشروبات الساخنة
-- ======================================
INSERT INTO categories (id, name, color) VALUES
(13, 'المشروبات الساخنة', '#A0522D');

INSERT INTO products (name, price, barcode, category_id) VALUES
('شاي', 15.00, '79', 13),
('قهوة سادة', 20.00, '80', 13),
('قهوة بالحليب', 25.00, '81', 13),
('نسكافيه', 25.00, '82', 13),
('لاتيه', 30.00, '83', 13),
('كابتشينو', 30.00, '84', 13),
('موكا', 35.00, '85', 13);

-- ======================================
-- 🍰 الفئة 14: الحلويات والمكملات
-- ======================================
INSERT INTO categories (id, name, color) VALUES
(14, 'الحلويات والمكملات', '#FF69B4');

INSERT INTO products (name, price, barcode, category_id) VALUES
('أرز باللبن', 25.00, '86', 14),
('أرز باللبن بالمكسرات', 35.00, '87', 14),
('مهلبية', 25.00, '88', 14),
('مهلبية بالمكسرات', 35.00, '89', 14),
('بودنج شوكولاتة', 30.00, '90', 14),
('كريم كراميل', 30.00, '91', 14),
('حلا الفواكه', 40.00, '92', 14),
('أيس كريم شوكولاتة', 35.00, '93', 14),
('أيس كريم مانجو', 35.00, '94', 14),
('أيس كريم فانيليا', 35.00, '95', 14),
('كيك شوكولاتة', 45.00, '96', 14),
('كيك فانيليا', 45.00, '97', 14);
