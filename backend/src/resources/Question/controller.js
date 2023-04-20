const db = require('../../services/db')
const logger = require('./../../common/logger')

const create = (req, res, next) => {
    const data = {
        title : req.body.title,
        description : req.body.description,
        topic : req.body.topic,
        difficulty: req.body.difficulty,
        acceptance: req.body.acceptance,
    }
    const { title, description, topic, difficulty, acceptance} = data;
    db.connect().then((db) => {
        // Check if a question with the same title already exists
        db.get('SELECT * FROM question WHERE title = ?', title, (err, row) => {
            if (err) {
                logger.info(err.message)
                return res.status(404).json({ message: err.message });
            }
            if (row) {
                return res.status(409).json({ message: 'A question with this title already exists.' });
            }
            
            db.run('INSERT INTO question (title, description, topic, difficulty, acceptance) VALUES (?, ?, ?, ?, ?)',
                title,
                description,
                topic,
                difficulty,
                acceptance,
                (err) => {
                    if (err){
                        logger.info(err.message)
                        return res.status(404).json({message: err.message});
                    }
                    logger.info("Successfully created question");
                    res.status(200).json({ message: 'Successfully created question' });
                }
            );
        });
    }).catch((error) => {
        logger.info(error);
        res.status(404).json({ message: err.message });
    });
}


const getQuestion = (req, res, next) => {
    const user_id = req.params.id;
    db.connect().then((db) =>{
        db.all(`SELECT * FROM question WHERE id=?`,[user_id],(err,data) => {
            if (err){
                logger.info(err.message)
                return res.status(404).json({message: err.message});
            }
            logger.info("Succesfully got Question")
            res.status(200).json(data);
        });
    })
    .catch((error) => {
        logger.info(error);
        res.status(404).json({message: err.message})
      });

}



const getAllQuestion = (req, res, next) => {
    db.connect().then((db) =>{
        db.all(`SELECT * FROM question`,[],(err,data) => {
            if (err){
                logger.info(err.message)
                return res.status(404).json({message: err.message});
            }
            logger.info("Succesfully got All Question")
            res.status(200).json(data);
        });
    })
    .catch((error) => {
        logger.info(error);
        res.status(404).json({message: err.message})
      });
}


const deleteQuestion = (req, res, next ) => {
    const user_id = req.params.id
    db.connect().then((db) =>{
        db.run(`DELETE FROM question WHERE id=?`, [user_id], (err) => {
            if (err){
                logger.info(err.message)
                return res.status(404).json({message: err.message});
            } 
            logger.info("Succesfully Deleted Question");
            res.status(200).json({message : 'Succesfully Deleted Question'})
        });
    })
    .catch((error) => {
        logger.info(error);
        res.status(404).json({message: err.message})
      });

}

const updateQuestion = (req, res, next) => {
    const user_id = req.params.id
    const updateFields = req.body

    if (Object.keys(updateFields).length === 0) {
        return res.status(400).json({message: 'No fields to update'})
    }

    let setClause = ''
    const validparameters = []

    for (const [field, value] of Object.entries(updateFields)) {
        setClause += `${field} = ?, `
        validparameters.push(value)
    }

    setClause = setClause.slice(0, -2)

    db.connect().then((db) =>{
        db.run(`UPDATE question SET ${setClause} WHERE id = ?`, [...validparameters, user_id], (err) => {
                if (err){
                    logger.info(err.message)
                    return res.status(404).json({message: err.message});
                } 
                logger.info("Succesfully Updated Question");
                res.status(200).json({message : 'Succesfully Updated Question'})
            });
        })
        .catch((error) => {
            logger.info(error);
            res.status(404).json({message: err.message})
        });
   

}

const filterQuestion = (req, res, next) => {
    const filter = req.body;

    if (Object.keys(filter).length === 0) {
        return res.status(404).json({ message: 'Empty filters' });
    }

    const whereConditions = Object.keys(filter).map(key => `${key} = ?`).join(' AND ');
    const values = Object.values(filter);

    db.connect().then((db) => {
        db.all(`SELECT * FROM question WHERE ${whereConditions}`, values, (err, data) => {
            if (err) {
                logger.info(err.message);
                return res.status(404).json({ message: err.message });
            }
            logger.info("Successfully filtered Questions ");
            res.status(200).json(data);
        });
    })
    .catch((error) => {
        logger.info(error);
        res.status(404).json({ message: error.message });
    });
}


module.exports = {
    create, 
    getAllQuestion,
    deleteQuestion,
    getQuestion,
    updateQuestion,
    filterQuestion
};