-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: almacen2
-- ------------------------------------------------------
-- Server version	8.0.39

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `atributo`
--

DROP TABLE IF EXISTS `atributo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atributo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `categoria_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `atributo_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atributo`
--

LOCK TABLES `atributo` WRITE;
/*!40000 ALTER TABLE `atributo` DISABLE KEYS */;
INSERT INTO `atributo` VALUES (1,'talla',1),(3,'numero de serie',5),(4,'marca',5),(5,'modelo',5);
/*!40000 ALTER TABLE `atributo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `atributo_producto`
--

DROP TABLE IF EXISTS `atributo_producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atributo_producto` (
  `atributo_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `valor` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  UNIQUE KEY `unique_producto_atributo_valor` (`producto_id`,`atributo_id`,`valor`),
  KEY `atributo_id` (`atributo_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `atributo_producto_ibfk_1` FOREIGN KEY (`atributo_id`) REFERENCES `atributo` (`id`),
  CONSTRAINT `atributo_producto_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atributo_producto`
--

LOCK TABLES `atributo_producto` WRITE;
/*!40000 ALTER TABLE `atributo_producto` DISABLE KEYS */;
INSERT INTO `atributo_producto` VALUES (1,3,'m'),(1,4,'p'),(1,5,'s'),(1,7,'39'),(1,8,'40'),(1,9,'42'),(1,10,'m'),(1,11,'L'),(1,12,'39'),(1,13,'40'),(1,14,'42'),(3,19,'654651328'),(4,19,'toshiba'),(5,19,'sn5');
/*!40000 ALTER TABLE `atributo_producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditoria_producto`
--

DROP TABLE IF EXISTS `auditoria_producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria_producto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `producto_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `campo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `valor_anterior` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `valor_nuevo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `producto_id` (`producto_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `auditoria_producto_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`),
  CONSTRAINT `auditoria_producto_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria_producto`
--

LOCK TABLES `auditoria_producto` WRITE;
/*!40000 ALTER TABLE `auditoria_producto` DISABLE KEYS */;
/*!40000 ALTER TABLE `auditoria_producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categoria`
--

DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categoria` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categoria`
--

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
INSERT INTO `categoria` VALUES (1,'EPPs'),(2,'CONSUMIBLES'),(5,'activo');
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_entrega`
--

DROP TABLE IF EXISTS `detalle_entrega`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_entrega` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entrega_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` int NOT NULL,
  `motivo` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `creado` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `entrega_id` (`entrega_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `detalle_entrega_ibfk_1` FOREIGN KEY (`entrega_id`) REFERENCES `entrega` (`id`),
  CONSTRAINT `detalle_entrega_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_entrega`
--

LOCK TABLES `detalle_entrega` WRITE;
/*!40000 ALTER TABLE `detalle_entrega` DISABLE KEYS */;
INSERT INTO `detalle_entrega` VALUES (24,7,3,1,'dotacion','2025-11-03 21:11:25','2025-11-03 21:11:25'),(25,7,7,1,'dotacion','2025-11-03 21:11:25','2025-11-03 21:11:25'),(26,7,14,1,'dotacion','2025-11-03 21:11:25','2025-11-03 21:11:25'),(27,7,11,1,'dotacion','2025-11-03 21:11:25','2025-11-03 21:11:25'),(28,8,3,1,'dotacion','2025-11-03 21:12:10','2025-11-03 21:12:10'),(29,8,8,1,'dotacion','2025-11-03 21:12:10','2025-11-03 21:12:10'),(30,8,10,1,'dotacion','2025-11-03 21:12:10','2025-11-03 21:12:10'),(31,8,12,1,'','2025-11-03 21:12:10','2025-11-03 21:12:10'),(32,9,18,1,'dotacion','2025-11-05 19:52:15','2025-11-05 19:52:15'),(33,10,18,1,'dotacion','2025-11-05 19:52:27','2025-11-05 19:52:27'),(38,6,3,1,'dotacion','2025-11-06 21:24:33','2025-11-06 21:24:33'),(39,6,9,1,'dotacion','2025-11-06 21:24:33','2025-11-06 21:24:33'),(40,6,12,1,'dotacion','2025-11-06 21:24:33','2025-11-06 21:24:33'),(41,6,10,1,'dotacion','2025-11-06 21:24:33','2025-11-06 21:24:33');
/*!40000 ALTER TABLE `detalle_entrega` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_remito`
--

DROP TABLE IF EXISTS `detalle_remito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_remito` (
  `id` int NOT NULL AUTO_INCREMENT,
  `producto_id` int NOT NULL,
  `remito_id` int NOT NULL,
  `cantidad` int NOT NULL,
  `creado` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `producto_id` (`producto_id`),
  KEY `remito_id` (`remito_id`),
  CONSTRAINT `detalle_remito_ibfk_1` FOREIGN KEY (`remito_id`) REFERENCES `remito` (`id`),
  CONSTRAINT `detalle_remito_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_remito`
--

LOCK TABLES `detalle_remito` WRITE;
/*!40000 ALTER TABLE `detalle_remito` DISABLE KEYS */;
INSERT INTO `detalle_remito` VALUES (9,3,4,15,'2025-11-03 21:04:58','2025-11-03 21:04:58'),(10,4,4,15,'2025-11-03 21:04:58','2025-11-03 21:04:58'),(11,5,4,5,'2025-11-03 21:04:58','2025-11-03 21:04:58'),(12,7,4,15,'2025-11-03 21:04:58','2025-11-03 21:04:58'),(13,8,4,15,'2025-11-03 21:04:58','2025-11-03 21:04:58'),(14,9,4,5,'2025-11-03 21:04:58','2025-11-03 21:04:58'),(15,10,4,10,'2025-11-03 21:04:58','2025-11-03 21:04:58'),(16,11,4,20,'2025-11-03 21:04:58','2025-11-03 21:04:58'),(17,12,4,15,'2025-11-03 21:04:58','2025-11-03 21:04:58'),(18,13,4,15,'2025-11-03 21:04:58','2025-11-03 21:04:58'),(19,14,4,5,'2025-11-03 21:04:58','2025-11-03 21:04:58'),(20,17,5,10,'2025-11-05 19:51:27','2025-11-05 19:51:27'),(21,18,5,2,'2025-11-05 19:51:27','2025-11-05 19:51:27'),(22,16,5,15,'2025-11-05 19:51:27','2025-11-05 19:51:27'),(23,15,5,10,'2025-11-05 19:51:27','2025-11-05 19:51:27');
/*!40000 ALTER TABLE `detalle_remito` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entrega`
--

DROP TABLE IF EXISTS `entrega`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entrega` (
  `id` int NOT NULL AUTO_INCREMENT,
  `trabajador_id` int NOT NULL,
  `fecha` date NOT NULL,
  `campo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `inspector` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `usuario_id` int NOT NULL,
  `creado` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `trabajador_id` (`trabajador_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `entrega_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`),
  CONSTRAINT `entrega_ibfk_2` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajador` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entrega`
--

LOCK TABLES `entrega` WRITE;
/*!40000 ALTER TABLE `entrega` DISABLE KEYS */;
INSERT INTO `entrega` VALUES (6,1,'2025-11-03','rio grande','felix',5,'2025-11-03 21:06:05','2025-11-03 21:06:05'),(7,4,'2025-11-03','rio grande','felix',5,'2025-11-03 21:11:25','2025-11-03 21:11:25'),(8,2,'2025-11-04','rio grande','felix',5,'2025-11-03 21:12:10','2025-11-03 21:12:10'),(9,4,'2025-11-05','rio grande','felix',5,'2025-11-05 19:52:15','2025-11-05 19:52:15'),(10,1,'2025-11-05','rio grande','felix',5,'2025-11-05 19:52:27','2025-11-05 19:52:27');
/*!40000 ALTER TABLE `entrega` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producto`
--

DROP TABLE IF EXISTS `producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `producto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `categoria_id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `unidad` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `creado` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto`
--

LOCK TABLES `producto` WRITE;
/*!40000 ALTER TABLE `producto` DISABLE KEYS */;
INSERT INTO `producto` VALUES (3,1,'camisa','unidad',12,'2025-10-16 19:48:37','2025-11-06 21:24:33'),(4,1,'camisa','unidad',15,'2025-10-16 19:52:05','2025-11-03 21:04:58'),(5,1,'camisa','unidad',5,'2025-10-16 19:56:10','2025-11-03 21:04:58'),(7,1,'pantalon','unidad',14,'2025-11-03 20:59:52','2025-11-03 21:11:25'),(8,1,'pantalon','unidad',14,'2025-11-03 21:00:07','2025-11-03 21:12:10'),(9,1,'pantalon','unidad',4,'2025-11-03 21:00:22','2025-11-06 21:24:33'),(10,1,'overol','unidad',8,'2025-11-03 21:00:45','2025-11-06 21:24:33'),(11,1,'overol','unidad',19,'2025-11-03 21:01:07','2025-11-03 21:11:25'),(12,1,'botines','par',13,'2025-11-03 21:01:42','2025-11-06 21:24:33'),(13,1,'botines','par',15,'2025-11-03 21:01:57','2025-11-03 21:04:58'),(14,1,'botines','par',4,'2025-11-03 21:02:12','2025-11-03 21:11:25'),(15,1,'protector auditivo de copa','par',10,'2025-11-05 19:47:32','2025-11-05 19:51:27'),(16,2,'filtro 6007','unidad',15,'2025-11-05 19:48:16','2025-11-05 19:51:27'),(17,2,'mica facial','unidad',10,'2025-11-05 19:48:38','2025-11-05 19:51:27'),(18,1,'protector respiratorio 3M 8515','unidad',0,'2025-11-05 19:49:22','2025-11-05 19:52:27'),(19,5,'handie','unidad',0,'2025-11-05 20:30:41','2025-11-05 20:30:41');
/*!40000 ALTER TABLE `producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `remito`
--

DROP TABLE IF EXISTS `remito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `remito` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `tipo_remito_id` int NOT NULL,
  `fecha` date NOT NULL,
  `señores` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `atencion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `contrato` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `numero` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `campo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `orden` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `observaciones` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `despachado` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `transportado` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `placa` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `recibido` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `creado` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `tipo_remito_id` (`tipo_remito_id`),
  CONSTRAINT `remito_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`),
  CONSTRAINT `remito_ibfk_2` FOREIGN KEY (`tipo_remito_id`) REFERENCES `tipo_remito` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remito`
--

LOCK TABLES `remito` WRITE;
/*!40000 ALTER TABLE `remito` DISABLE KEYS */;
INSERT INTO `remito` VALUES (4,5,1,'2025-11-03','confipetrol','rio grande','000021231','00001','rio grande','3212154321','no','cumpa','felix','3652asd','mauricio','2025-11-03 21:04:58','2025-11-03 21:04:58'),(5,5,1,'2025-11-05','confipetrol','rio grande','000021231','00002','rio grande','32121543654','no','cumpa','felix','3652asd','mauricio','2025-11-05 19:51:27','2025-11-05 19:51:27');
/*!40000 ALTER TABLE `remito` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_remito`
--

DROP TABLE IF EXISTS `tipo_remito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_remito` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_remito`
--

LOCK TABLES `tipo_remito` WRITE;
/*!40000 ALTER TABLE `tipo_remito` DISABLE KEYS */;
INSERT INTO `tipo_remito` VALUES (1,'ingreso'),(2,'egreso');
/*!40000 ALTER TABLE `tipo_remito` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trabajador`
--

DROP TABLE IF EXISTS `trabajador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trabajador` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `apellido_paterno` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `apellido_materno` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `cargo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `nacimiento` date NOT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trabajador`
--

LOCK TABLES `trabajador` WRITE;
/*!40000 ALTER TABLE `trabajador` DISABLE KEYS */;
INSERT INTO `trabajador` VALUES (1,'julio cesar','sempertegui','tiefenbock','asistencia electrica','1995-04-29','76384041'),(2,'jose','sempertegui','tiefenbock','asistencia electrica','2025-10-07','76384041'),(4,'juan carlos','rios','','asistencia mecanica A','1985-08-03','76384085');
/*!40000 ALTER TABLE `trabajador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `apellido` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `usuario` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `contraseña` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `rol` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (5,'Admin','Admin','admin','$2y$10$WnLsVi2IkK.1qsfV6lXL1uhgnq80IAhdpeFMbgc2HNqnLREmrmWu2','admin'),(7,'usuario','usuario','usuario','$2y$10$9OQ.HUagCXNRcTRpN.XK7uciluQdYukk5uIl.Ej1SRrcgxvMjM5uO','usuario'),(8,'felix','araujo','felix','$2y$10$wcwxzUXynJXL5fovtJkcZumDM0jhb4owm0tHQpKOeh4FSI6Dtt8Wu','usuario');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-07 22:40:22
