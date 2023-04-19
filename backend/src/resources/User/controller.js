const db = require('../../services/db')

const create = (req, res, next) => {
    const data = {
        username : req.body.username,
        password: req.body.password,
        email: req.body.email,
        full_name: req.body.full_name,
    }
    const { username, password, email, full_name } = data;
    db.connect().then((db) => {
        db.run(
          'INSERT INTO users (username, password, email, full_name) VALUES (?, ?, ?, ?)',
          username,
          password,
          email,
          full_name,
         (err) => {
            if (err){
                console.error(err.message)
                return res.status(404).json({message: err.message});
            }    
        console.log("Succesfully Created User");
        res.status(200).json({ message: 'Succesfully Created User' });
        }
        );
      }).catch((error) => {
        console.error(error);
        res.status(404).json({message: err.message})
      });

}

const getUser = (req, res, next) => {
    const user_id = req.params.id;
    db.connect().then((db) =>{
        db.all(`SELECT * FROM users WHERE id=?`,[user_id],(err,data) => {
            if (err){
                console.error(err.message)
                return res.status(404).json({message: err.message});
            }
            console.log("Succesfully got User")
            res.status(200).json(data);
        });
    })
    .catch((error) => {
        console.error(error);
        res.status(404).json({message: err.message})
      });

}



const getAllUsers = (req, res, next) => {
    db.connect().then((db) =>{
        db.all(`SELECT * FROM users`,[],(err,data) => {
            if (err){
                console.error(err.message)
                return res.status(404).json({message: err.message});
            }
            console.log("Succesfully got All Users")
            res.status(200).json(data);
        });
    })
    .catch((error) => {
        console.error(error);
        res.status(404).json({message: err.message})
      });
}


const deleteUser = (req, res, next ) => {
    const user_id = req.params.id
    db.connect().then((db) =>{
        db.run(`DELETE FROM users WHERE id=?`, [user_id], (err) => {
            if (err){
                console.error(err.message)
                return res.status(404).json({message: err.message});
            } 
            console.log("Succesfully Deleted User");
            res.status(200).json({message : 'Succesfully Deleted User'})
        });
    })
    .catch((error) => {
        console.error(error);
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
                    console.error(err.message)
                    return res.status(404).json({message: err.message});
                } 
                console.log("Succesfully Updated User");
                res.status(200).json({message : 'Succesfully Updated User'})
            });
        })
        .catch((error) => {
            console.error(error);
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