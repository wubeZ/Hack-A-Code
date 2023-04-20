const jwt = require('jsonwebtoken');
require('../common/env')

const auth = (req, res, next) => {
  const bearer = req.headers.authorization;
  if (!bearer){
    return res.status(401).json({ message: 'Unauthorized' });
  }
  const token = bearer.toString().split(' ')[1]

  if (!token) {
    return res.status(401).json({ message: 'Unauthorized' });
  }

  try {

    const decoded = jwt.verify(token, process.env.JWT_SECRET_KEY);
    req.userId = decoded.userId;
    next();

  } catch (err) {
    return res.status(401).json({ message: 'Unauthorized' });
  }
}

module.exports = auth;
