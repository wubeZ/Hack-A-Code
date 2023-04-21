const db = require("../../services/db");
const tester = require("../../../Test-Checker/tester");
const logger = require("../../common/logger");

const create = (req, res, next) => {
  const data = {
    question: req.body.question,
    path: req.body.path,
    status: req.body.status,
  };
  const { question, path } = data;

  const result = tester.test(question, path);
  data.status = result;

  db.connect()
    .then((db) => {
      if (data.status === 1) {
        db.all(`SELECT * FROM question WHERE id=?`, [question], (err, row) => {
          if (err) {
            logger.info(err.message);
            return res.status(404).json({ message: err.message });
          }
          if (!row){
            return res.status(404).json({ message: "No question found"});
          }
          if (row[0].status === 0) {
            const new_status = 1;
            const question_id = row[0].id;
            const user_id = req.userId;

            db.run(
              `UPDATE question SET status = ? WHERE id = ?`,
              [new_status, question_id],
              (err) => {
                if (err) {
                  logger.info(err.message);
                  return res.status(404).json({ message: err.message });
                }
              }
            );

            db.all(`SELECT * FROM users WHERE id=?`, [user_id], (err, data) => {
              if (err) {
                logger.info(err.message);
                return res.status(404).json({ message: err.message });
              }
              const new_count = data[0].question_count + 1;
              db.run(
                `UPDATE users SET question_count = ? WHERE id = ?`,
                [new_count, user_id],
                (err) => {
                  if (err) {
                    logger.info(err.message);
                    return res.status(404).json({ message: err.message });
                  }
                  logger.info(
                    "Succesfully Changed Status of Question and User Question Count"
                  );
                }
              );
            });
          }
        });
      }

      db.run(
        "INSERT INTO submission (question,path,status) VALUES (?, ?, ?)",
        question,
        path,
        data.status,
        (err) => {
          if (err) {
            logger.info(err.message);
            return res.status(404).json({ message: err.message });
          }
          logger.info("Succesfully Created Submission");
          res.status(200).json({
            message: `Succesfully Created Submission: ${data.status}`,
          });
        }
      );
    })
    .catch((error) => {
      logger.info(error);
      res.status(404).json({ message: err.message });
    });
};

const getSubmission = (req, res, next) => {
  const user_id = req.params.id;
  db.connect()
    .then((db) => {
      db.all(`SELECT * FROM submission WHERE id=?`, [user_id], (err, data) => {
        if (err) {
          logger.info(err.message);
          return res.status(404).json({ message: err.message });
        }
        logger.info("Succesfully got Submission");
        res.status(200).json(data);
      });
    })
    .catch((error) => {
      logger.info(error);
      res.status(404).json({ message: err.message });
    });
};

const getAllSubmission = (req, res, next) => {
  db.connect()
    .then((db) => {
      db.all(`SELECT * FROM submission`, [], (err, data) => {
        if (err) {
          logger.info(err.message);
          return res.status(404).json({ message: err.message });
        }
        logger.info("Succesfully got All Submission");
        res.status(200).json(data);
      });
    })
    .catch((error) => {
      logger.info(error);
      res.status(404).json({ message: err.message });
    });
};

const deleteSubmission = (req, res, next) => {
  const user_id = req.params.id;
  db.connect()
    .then((db) => {
      db.run(`DELETE FROM submission WHERE id=?`, [user_id], (err) => {
        if (err) {
          logger.info(err.message);
          return res.status(404).json({ message: err.message });
        }
        logger.info("Succesfully Deleted Submission");
        res.status(200).json({ message: "Succesfully Deleted Submission" });
      });
    })
    .catch((error) => {
      logger.info(error);
      res.status(404).json({ message: err.message });
    });
};

const updateSubmission = (req, res, next) => {
  const user_id = req.params.id;
  const updateFields = req.body;

  if (Object.keys(updateFields).length === 0) {
    return res.status(400).json({ message: "No fields to update" });
  }

  let setClause = "";
  const validparameters = [];

  for (const [field, value] of Object.entries(updateFields)) {
    setClause += `${field} = ?, `;
    validparameters.push(value);
  }

  setClause = setClause.slice(0, -2);

  db.connect()
    .then((db) => {
      db.run(
        `UPDATE submission SET ${setClause} WHERE id = ?`,
        [...validparameters, user_id],
        (err) => {
          if (err) {
            logger.info(err.message);
            return res.status(404).json({ message: err.message });
          }
          logger.info("Succesfully Updated Submission");
          res.status(200).json({ message: "Succesfully Updated Submission" });
        }
      );
    })
    .catch((error) => {
      logger.info(error);
      res.status(404).json({ message: err.message });
    });
};

const filterSubmission = (req, res, next) => {
  const filter = req.body;

  if (Object.keys(filter).length === 0) {
    return res.status(404).json({ message: "Empty filters" });
  }

  const whereConditions = Object.keys(filter)
    .map((key) => `${key} = ?`)
    .join(" AND ");
  const values = Object.values(filter);

  db.connect()
    .then((db) => {
      db.all(
        `SELECT * FROM submission WHERE ${whereConditions}`,
        values,
        (err, data) => {
          if (err) {
            logger.info(err.message);
            return res.status(404).json({ message: err.message });
          }
          logger.info("Successfully filtered Submissions ");
          res.status(200).json(data);
        }
      );
    })
    .catch((error) => {
      logger.info(error);
      res.status(404).json({ message: error.message });
    });
};

module.exports = {
  create,
  getAllSubmission,
  getSubmission,
  updateSubmission,
  deleteSubmission,
  filterSubmission,
};
