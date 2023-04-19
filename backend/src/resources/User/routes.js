const router = require('express').Router()
const userController = require('./controller')

router
.route('/')
.get(userController.getAllUsers)
.post(userController.create)

router
.route('/:id')
.get(userController.getUser)
.put(userController.updateUser)
.delete(userController.deleteUser)

module.exports = router;
