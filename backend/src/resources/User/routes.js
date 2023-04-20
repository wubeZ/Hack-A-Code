const router = require('express').Router()
const userController = require('./controller')
const auth = require('../../middlewares/auth')

router
.route('/')
.get(userController.getAllUsers)
.post(userController.create)

router
.route('/:id')
.get(auth, userController.getUser)
.put(auth, userController.updateUser)
.delete(auth, userController.deleteUser)

module.exports = router;
