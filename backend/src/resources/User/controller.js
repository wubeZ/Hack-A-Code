const db = require('../../services/db')
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const logger = require('./../../common/logger')
require('./../../common/env')

const create = (req, res, next) => {
  const { username, password, email, full_name } = req.body;

  // Check if email already exists in the database
  db.connect().then((db) =>{
      db.run('SELECT * FROM users WHERE email = ?', [email], (err, row) => {
        if (err) {
          logger.info(err.message);
          return res.status(500).json({ message: 'Server Error' });
        }

        if (row) {
          return res.status(400).json({ message: 'Email already exists' });
        }
      });

        bcrypt.hash(password, 10, (err, hash) => {
          if (err) {
            logger.info(err.message);
            return res.status(500).json({ message: 'Server Error' });
          }

          const newUser = {
            username,
            password: hash,
            email,
            full_name,
          };

          db.run(
            'INSERT INTO users (username, password, email, full_name) VALUES (?, ?, ?, ?)',
            [newUser.username, newUser.password, newUser.email, newUser.full_name],
            (err) => {
              if (err) {
                logger.info(err.message);
                return res.status(500).json({ message: 'Server Error' });
              }

              db.all(`SELECT * FROM users WHERE email = ?`,[newUser.email],(err,data) => {
                if (err){
                    logger.info(err.message)
                    return res.status(404).json({message: err.message});
                }
                
                const token = jwt.sign(
                  { email: newUser.email,
                  userId: data[0].id },
                  process.env.JWT_SECRET_KEY,
                  { expiresIn: '100y' }
                );
                res.status(201).json({ message: 'User created', token });
            });
            }
          );
        });
        
      })
      .catch((error) => {
        logger.info(error);
        res.status(404).json({message: err.message})
      });

}

const getUser = (req, res, next) => {
    const user_id = req.params.id;
    db.connect().then((db) =>{
        db.all(`SELECT * FROM users WHERE id=?`,[user_id],(err,data) => {
            if (err){
                logger.info(err.message)
                return res.status(404).json({message: err.message});
            }
            logger.info("Succesfully got User")
            res.status(200).json(data);
        });
    })
    .catch((error) => {
        logger.info(error);
        res.status(404).json({message: err.message})
      });

}



const getAllUsers = (req, res, next) => {
    db.connect().then((db) =>{
        db.all(`SELECT * FROM users`,[],(err,data) => {
            if (err){
                logger.info(err.message)
                return res.status(404).json({message: err.message});
            }
            logger.info("Succesfully got All Users")
            res.status(200).json(data);
        });
    })
    .catch((error) => {
        logger.info(error);
        res.status(404).json({message: err.message})
      });
}


const deleteUser = (req, res, next ) => {
    const user_id = req.params.id
    db.connect().then((db) =>{
        db.run(`DELETE FROM users WHERE id=?`, [user_id], (err) => {
            if (err){
                logger.info(err.message)
                return res.status(404).json({message: err.message});
            } 
            logger.info("Succesfully Deleted User");
            res.status(200).json({message : 'Succesfully Deleted User'})
        });
    })
    .catch((error) => {
        logger.info(error);
        res.status(404).json({message: err.message})
      });

}

const updateUser = (req, res, next) => {
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
        db.run(`UPDATE users SET ${setClause} WHERE id = ?`, [...validparameters, user_id], (err) => {
                if (err){
                    logger.info(err.message)
                    return res.status(404).json({message: err.message});
                } 
                logger.info("Succesfully Updated User");
                res.status(200).json({message : 'Succesfully Updated User'})
            });
        })
        .catch((error) => {
            logger.info(error);
            res.status(404).json({message: err.message})
        });
   

}


module.exports = {
    create, 
    getAllUsers,
    deleteUser,
    getUser,
    updateUser
};