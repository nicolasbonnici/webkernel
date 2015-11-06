-- -----------------------------------------------------
-- Table `dummy`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dummy` (
  `iddummy` INT(15) NOT NULL AUTO_INCREMENT,
  `test_string` VARCHAR(45) NOT NULL,
  `test_int` INT(15) NOT NULL,
  `test_float` FLOAT NOT NULL,
  `test_null` VARCHAR(45) NULL,
  `lastupdate` INT(15) NULL,
  `created` INT(15) NULL,
  `dummy4_iddummy4` INT(15) NULL,
  PRIMARY KEY (`iddummy`),
  INDEX `fk_dummy_dummy41_idx` (`dummy4_iddummy4` ASC),
  CONSTRAINT `fk_dummy_dummy41`
    FOREIGN KEY (`dummy4_iddummy4`)
    REFERENCES `nbonnici`.`dummy4` (`iddummy4`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;