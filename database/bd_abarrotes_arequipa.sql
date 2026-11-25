CREATE DATABASE IF NOT EXISTS bd_abarrotes_arequipa
CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE bd_abarrotes_arequipa;

CREATE TABLE usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombres VARCHAR(80) NOT NULL,
  apellidos VARCHAR(80) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  telefono VARCHAR(15) NOT NULL,
  rol ENUM('admin', 'cliente') DEFAULT 'cliente',
  estado TINYINT(1) DEFAULT 1,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE distritos_arequipa (
  id_distrito INT AUTO_INCREMENT PRIMARY KEY,
  nombre_distrito VARCHAR(80) NOT NULL,
  costo_delivery DECIMAL(6,2) NOT NULL,
  estado TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE categorias (
  id_categoria INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL,
  descripcion VARCHAR(180)
) ENGINE=InnoDB;

CREATE TABLE productos (
  id_producto INT AUTO_INCREMENT PRIMARY KEY,
  id_categoria INT NOT NULL,
  codigo_sku VARCHAR(20) NOT NULL UNIQUE,
  nombre VARCHAR(120) NOT NULL,
  descripcion TEXT,
  precio_venta DECIMAL(8,2) NOT NULL,
  stock_actual INT NOT NULL DEFAULT 0,
  imagen_url VARCHAR(255),
  estado TINYINT(1) DEFAULT 1,
  FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
) ENGINE=InnoDB;

CREATE TABLE pedidos_ventas (
  id_pedido INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  id_distrito INT NOT NULL,
  direccion_exacta VARCHAR(180) NOT NULL,
  referencia VARCHAR(150),
  subtotal DECIMAL(8,2) NOT NULL,
  costo_delivery DECIMAL(6,2) NOT NULL,
  total_pagar DECIMAL(8,2) NOT NULL,
  metodo_pago ENUM('Yape/Plin', 'Contra entrega') NOT NULL,
  estado_pedido ENUM('Pendiente', 'En camino', 'Entregado', 'Cancelado') DEFAULT 'Pendiente',
  fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_cliente) REFERENCES usuarios(id_usuario),
  FOREIGN KEY (id_distrito) REFERENCES distritos_arequipa(id_distrito)
) ENGINE=InnoDB;

CREATE TABLE detalle_pedido (
  id_detalle INT AUTO_INCREMENT PRIMARY KEY,
  id_pedido INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(8,2) NOT NULL,
  subtotal_linea DECIMAL(8,2) NOT NULL,
  FOREIGN KEY (id_pedido) REFERENCES pedidos_ventas(id_pedido) ON DELETE CASCADE,
  FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
) ENGINE=InnoDB;

INSERT INTO usuarios (nombres, apellidos, email, password, telefono, rol) VALUES
('Carlos Daniel', 'Quispe Beraun', 'admin@abarrotesarequipa.pe',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '901168539', 'admin');

INSERT INTO distritos_arequipa (nombre_distrito, costo_delivery) VALUES
('Arequipa (Cercado)', 4.00),
('Yanahuara', 5.00),
('Cayma', 6.00),
('Cerro Colorado', 7.00),
('José Luis Bustamante y Rivero', 5.00),
('Paucarpata', 6.00),
('Miraflores', 5.00),
('Mariano Melgar', 6.00),
('Alto Selva Alegre', 6.00),
('Socabaya', 7.00),
('Jacobo Hunter', 6.00),
('Sachaca', 6.00),
('Tiabaya', 8.00),
('Characato', 9.00),
('Sabandía', 8.00),
('Uchumayo', 9.00);

INSERT INTO categorias (nombre, descripcion) VALUES
('Granos y Menestras', 'Arroz extra, azúcar rubia y blanca, lentejas, quinua y menestras seleccionadas'),
('Aceites y Condimentos', 'Aceites vegetales, sal de mesa, vinagres y condimentos'),
('Lácteos y Desayunos', 'Leche evaporada, avena, café, cocoa y cereales'),
('Fideos y Conservas', 'Fideos tallarín y canuto, filete de atún y conservas'),
('Artículos de Limpieza', 'Detergentes, lejía, lavavajillas y papel higiénico');

-- Catálogo inicial de 40 Productos
INSERT INTO productos (id_categoria, codigo_sku, nombre, descripcion, precio_venta, stock_actual, imagen_url) VALUES
-- Categoría 1: Granos y Menestras
(1, 'ABR-001', 'Arroz Costeño Extra 5 Kg', 'Bolsa de arroz superior granos enteros x 5 kg', 22.50, 45, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=500'),
(1, 'ABR-002', 'Azúcar Rubia Cartavio 1 Kg', 'Azúcar rubia doméstica bolsa x 1 kg', 4.20, 60, 'https://images.unsplash.com/photo-1581441363689-1f3c3c414635?w=500'),
(1, 'ABR-003', 'Lenteja Serranita 500 g', 'Menestra seleccionada bolsa x 500 gramos', 4.80, 35, 'https://images.unsplash.com/photo-1515543904379-3d757afe72e4?w=500'),
(1, 'ABR-013', 'Arroz Paisana Superior 750 g', 'Bolsa de arroz blanco seleccionado x 750 g', 3.60, 70, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=500'),
(1, 'ABR-014', 'Quinua Blanca Perlada 500 g', 'Grano andino seleccionado bolsa x 500 g', 6.80, 32, 'https://images.unsplash.com/photo-1515543904379-3d757afe72e4?w=500'),
(1, 'ABR-015', 'Frejol Canario Costeño 500 g', 'Frejol canario limpio de primera x 500 g', 5.90, 28, 'https://images.unsplash.com/photo-1515543904379-3d757afe72e4?w=500'),
(1, 'ABR-016', 'Arveja Verde Partida 500 g', 'Menestra seca partida lista para cocinar x 500 g', 3.90, 40, 'https://images.unsplash.com/photo-1515543904379-3d757afe72e4?w=500'),
(1, 'ABR-017', 'Maíz Cancha Serrana 500 g', 'Maíz chulpi seleccionado para tostar x 500 g', 4.50, 36, 'https://images.unsplash.com/photo-1515543904379-3d757afe72e4?w=500'),
(1, 'ABR-018', 'Azúcar Blanca Paramonga 1 Kg', 'Azúcar blanca refinada bolsa x 1 kg', 4.60, 50, 'https://images.unsplash.com/photo-1581441363689-1f3c3c414635?w=500'),

-- Categoría 2: Aceites y Condimentos
(2, 'ABR-004', 'Aceite Vegetal Primor 900 ml', 'Aceite vegetal premium botella x 900 ml', 8.90, 50, 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=500'),
(2, 'ABR-005', 'Sal de Cocina Emsal 1 Kg', 'Sal yodada de mesa bolsa x 1 kg', 2.00, 80, 'https://images.unsplash.com/photo-1626197031507-c17099753214?w=500'),
(2, 'ABR-019', 'Aceite Vegetal Cil 900 ml', 'Aceite vegetal 100% soya botella x 900 ml', 8.20, 44, 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=500'),
(2, 'ABR-020', 'Aceite de Oliva Extra Virgen 500 ml', 'Aceite de oliva prensado en frío botella x 500 ml', 24.90, 18, 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=500'),
(2, 'ABR-021', 'Vinagre Blanco Venturo 500 ml', 'Vinagre blanco de alcohol botella x 500 ml', 2.80, 38, 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=500'),
(2, 'ABR-022', 'Sillao Kikko Clásico 500 ml', 'Salsa de soya fermentada botella x 500 ml', 4.90, 30, 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=500'),
(2, 'ABR-023', 'Mayonesa Alacena Doypack 475 g', 'Mayonesa receta casera envase doy pack x 475 g', 7.50, 42, 'https://images.unsplash.com/photo-1626197031507-c17099753214?w=500'),
(2, 'ABR-024', 'Ají Amarillo Molido Alacena 250 g', 'Pasta de ají amarillo peruano doy pack x 250 g', 4.20, 35, 'https://images.unsplash.com/photo-1626197031507-c17099753214?w=500'),

-- Categoría 3: Lácteos y Desayunos
(3, 'ABR-006', 'Leche Evaporada Gloria Azul 400 g', 'Leche evaporada entera lata grande x 400 g', 4.30, 90, 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=500'),
(3, 'ABR-007', 'Avena 3 Ositos Clásica 300 g', 'Hojuelas de avena precocida bolsa x 300 g', 3.50, 40, 'https://images.unsplash.com/photo-1614961233913-a5113a4a34ed?w=500'),
(3, 'ABR-025', 'Leche Evaporada Ideal Cremosa 395 g', 'Leche evaporada enriquecida lata x 395 g', 4.10, 75, 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=500'),
(3, 'ABR-026', 'Leche UHT Gloria Entera Caja 1 L', 'Leche entera lista para tomar caja tetra pak x 1 L', 5.40, 48, 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=500'),
(3, 'ABR-027', 'Café Instantáneo Altomayo 170 g', 'Café soluble gourmet frasco de vidrio x 170 g', 18.50, 24, 'https://images.unsplash.com/photo-1614961233913-a5113a4a34ed?w=500'),
(3, 'ABR-028', 'Cocoa Winter’s Bolsa 160 g', 'Polvo de cacao para preparar desayuno x 160 g', 4.70, 40, 'https://images.unsplash.com/photo-1614961233913-a5113a4a34ed?w=500'),
(3, 'ABR-029', 'Mermelada de Fresa Gloria 320 g', 'Mermelada tradicional vaso de vidrio x 320 g', 5.60, 29, 'https://images.unsplash.com/photo-1614961233913-a5113a4a34ed?w=500'),
(3, 'ABR-030', 'Mantequilla Gloria con Sal 200 g', 'Mantequilla pasteurizada barra x 200 g', 8.80, 22, 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=500'),

-- Categoría 4: Fideos y Conservas
(4, 'ABR-008', 'Fideo Tallarín Don Vittorio 500 g', 'Pasta larga de sémola de trigo x 500 g', 3.20, 55, 'https://images.unsplash.com/photo-1551462147-ff29053bfc14?w=500'),
(4, 'ABR-009', 'Filete de Atún Florida 170 g', 'Filete de atún en aceite vegetal lata x 170 g', 6.50, 48, 'https://images.unsplash.com/photo-1534604973900-c43ab4c2e0ab?w=500'),
(4, 'ABR-031', 'Fideo Canuto Chico Anita 500 g', 'Pasta corta para sopas y guisos x 500 g', 2.90, 60, 'https://images.unsplash.com/photo-1551462147-ff29053bfc14?w=500'),
(4, 'ABR-032', 'Fideo Tornillo Molitalia 500 g', 'Pasta espiral de trigo seleccionado x 500 g', 3.40, 52, 'https://images.unsplash.com/photo-1551462147-ff29053bfc14?w=500'),
(4, 'ABR-033', 'Trozos de Atún Campomar 170 g', 'Trozos de atún en agua y sal lata x 170 g', 5.80, 46, 'https://images.unsplash.com/photo-1534604973900-c43ab4c2e0ab?w=500'),
(4, 'ABR-034', 'Salsa Roja Don Vittorio 400 g', 'Salsa lista de tomate y especias doy pack x 400 g', 4.50, 38, 'https://images.unsplash.com/photo-1551462147-ff29053bfc14?w=500'),
(4, 'ABR-035', 'Duraznos en Mitades Aconcagua 820 g', 'Conserva de duraznos en almíbar lata x 820 g', 9.90, 25, 'https://images.unsplash.com/photo-1534604973900-c43ab4c2e0ab?w=500'),

-- Categoría 5: Artículos de Limpieza
(5, 'ABR-010', 'Detergente Bolívar Floral 750 g', 'Detergente en polvo cuidado total x 750 g', 8.50, 30, 'https://images.unsplash.com/photo-1585421514284-efb74c2b69ba?w=500'),
(5, 'ABR-011', 'Lejía Clorox Tradicional 1 L', 'Desinfectante blanqueador botella x 1 litro', 4.60, 42, 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=500'),
(5, 'ABR-012', 'Papel Higiénico Elite Doble Hoja x 4', 'Paquete de 4 rollos suave doble hoja', 5.80, 38, 'https://images.unsplash.com/photo-1584556812952-905ffd0c611a?w=500'),
(5, 'ABR-036', 'Detergente Ace Limón 750 g', 'Detergente en polvo limpieza profunda x 750 g', 8.20, 34, 'https://images.unsplash.com/photo-1585421514284-efb74c2b69ba?w=500'),
(5, 'ABR-037', 'Lavavajillas Ayudín Limón 900 g', 'Pasta lavavajillas concentrada pote x 900 g', 6.90, 31, 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=500'),
(5, 'ABR-038', 'Suavizante Suavitel Primavera 800 ml', 'Acondicionador de telas botella x 800 ml', 7.40, 27, 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=500'),
(5, 'ABR-039', 'Jabón de Ropa Bolívar Azul 210 g', 'Jabón en barra para lavado a mano x 210 g', 2.60, 65, 'https://images.unsplash.com/photo-1585421514284-efb74c2b69ba?w=500'),
(5, 'ABR-040', 'Servilletas de Papel Scott x 100', 'Paquete de servilletas blancas dobladas x 100 hojas', 3.10, 50, 'https://images.unsplash.com/photo-1584556812952-905ffd0c611a?w=500');