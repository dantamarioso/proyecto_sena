-- Tabla para auditoría de cambios
CREATE TABLE IF NOT EXISTS auditoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    tabla VARCHAR(50) NOT NULL,
    registro_id INT NOT NULL,
    accion VARCHAR(20) NOT NULL, -- 'crear', 'actualizar', 'eliminar'
    campo_anterior LONGTEXT,
    campo_nuevo LONGTEXT,
    detalles JSON,
    admin_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_tabla_registro (tabla, registro_id),
    INDEX idx_fecha (fecha_creacion),
    INDEX idx_accion (accion)
);
