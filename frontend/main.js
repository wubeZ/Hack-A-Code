const { app, BrowserWindow } = require('electron')
const sqlite3 = require('sqlite3').verbose();

const createWindow = () => {
  const win = new BrowserWindow({
    width: 1400,
    height: 900,
    webPreferences: {
        nodeIntegration: true, // <--- flag
        nodeIntegrationInWorker: true // <---  for web workers
    }
  })

  win.loadFile('site/dashboard.html')
}

app.whenReady().then(() => {
  createWindow()

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createWindow()
    }
  })
})

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit()
  }
})