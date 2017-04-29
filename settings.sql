CREATE TABLE workflow (
    `id` INT NOT NULL AUTO_INCREMENT,
    `title` TEXT,
    `description` TEXT,
    `swms` TEXT,
    `created_at` DATETIME,
    `collected_at` DATETIME NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE tag (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` TEXT,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE tag_wf (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_tag` INT NOT NULL,
    `id_workflow` INT NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_workflow) REFERENCES workflow(id),
    FOREIGN KEY (id_tag) REFERENCES tag(id)
);

CREATE TABLE ontology (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` TEXT,
    `prefix` TEXT NOT NULL,
    `iri` TEXT NOT NULL,
    `color` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE ontology_concept (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_ontology` INT NOT NULL,
    `iri_parent` TEXT,
    `label` TEXT,
    `description` TEXT,
    `iri` TEXT NOT NULL,
    `short_form` TEXT NOT NULL,
    `obo_id` TEXT,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_ontology) REFERENCES ontology(id)
);

CREATE TABLE ontology_term (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_ontology_concept` INT NOT NULL,
    `string` TEXT NOT NULL,
    `type` TEXT NOT NULL,
    `source` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_ontology_concept) REFERENCES ontology_concept(id)
);

CREATE TABLE semantic_annotation (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_ontology_concept` INT NOT NULL,
    `id_workflow` INT NOT NULL,
    `annotation_type` TEXT NOT NULL,
    `distance` INT NOT NULL,
    `id_metadata` INT,
    `metadata_type` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_ontology_concept) REFERENCES ontology_concept(id),
    FOREIGN KEY (id_workflow) REFERENCES workflow(id),
    FOREIGN KEY (id_metadata) REFERENCES tag(id)
);

INSERT INTO ontology (`name`, `prefix`, `iri`, `color`, `created_at` )
VALUES ('edam', 'EDAM', 'http://edamontology.org', '#009688', NOW());

INSERT INTO ontology (`name`, `prefix`, `iri`, `color`, `created_at` )
VALUES ('cheminf', 'CHEMINF', 'http://purl.obolibrary.org/obo/cheminf', '#FF9800', NOW());
