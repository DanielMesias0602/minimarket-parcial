-- ============================================================
-- Base de datos: bodega_ventas
-- Minimarket "La Esquina" — Cañete
-- Codificación: utf8mb4 / utf8mb4_unicode_ci
-- ============================================================

CREATE DATABASE IF NOT EXISTS bodega_ventas
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE bodega_ventas;

-- ============================================================
-- Tabla: producto
-- ============================================================
CREATE TABLE IF NOT EXISTS producto (
    id_producto     INT             NOT NULL AUTO_INCREMENT,
    codigo          VARCHAR(20)     NOT NULL,
    nombre          VARCHAR(100)    NOT NULL,
    categoria       VARCHAR(50)     NOT NULL,
    precio_venta    DECIMAL(10,2)   NOT NULL,
    stock_actual    INT             NOT NULL DEFAULT 0,
    stock_minimo    INT             NOT NULL DEFAULT 0,
    -- Restricciones de integridad (RN03, RN04)
    CONSTRAINT pk_producto           PRIMARY KEY (id_producto),
    CONSTRAINT uq_producto_codigo    UNIQUE (codigo),
    CONSTRAINT ck_precio_no_negativo CHECK (precio_venta >= 0),
    CONSTRAINT ck_stock_actual       CHECK (stock_actual >= 0),
    CONSTRAINT ck_stock_minimo       CHECK (stock_minimo >= 0)
);

-- ============================================================
-- Tabla: venta
-- ============================================================
CREATE TABLE IF NOT EXISTS venta (
    id_venta        INT             NOT NULL AUTO_INCREMENT,
    id_producto     INT             NOT NULL,
    cantidad        INT             NOT NULL,
    precio_unitario DECIMAL(10,2)   NOT NULL,
    total           DECIMAL(10,2)   NOT NULL,
    fecha_hora      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Restricciones de integridad (RN01, RN02)
    CONSTRAINT pk_venta              PRIMARY KEY (id_venta),
    CONSTRAINT fk_venta_producto     FOREIGN KEY (id_producto)
                                         REFERENCES producto(id_producto),
    CONSTRAINT ck_cantidad_positiva  CHECK (cantidad > 0),
    CONSTRAINT ck_precio_unit_nn     CHECK (precio_unitario >= 0),
    CONSTRAINT ck_total_no_negativo  CHECK (total >= 0)
);
