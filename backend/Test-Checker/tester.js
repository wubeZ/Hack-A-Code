const fs = require("fs");
const path = require("path");
const { Console } = require("console");
const { spawn } = require("child_process");

function grab_output(input, num, name) {
  // globalThis.output = [];
  logger = `./Test-Checker/${num}/res.txt`;
  fs.truncate(logger, 0, (err) => {
    if (err) {
      console.error(err);
    } else {
      // console.log(`Contents of ${logger} deleted.`);
    }
  });
  const log = fs.createWriteStream(logger, { flags: "a" });
  const pythonProcess = spawn("python", [
    `./Test-Checker/${num}/submissions/${name}`,
  ]);

  for (let i = 0; i < input.length; i++) {
    pythonProcess.stdin.write(`${input[i]}\n`);
  }
  pythonProcess.stdin.end();

  pythonProcess.stdout.on("data", (data) => {
    // const lines = data.toString().split("\n");
    // lines.forEach((line) => {
    //   if (line !== "") {
    //     globalThis.output.push(line);
    //   }
    // });
    log.write(data);
  });

  pythonProcess.stderr.on("data", (data) => {
    console.error(`Python error: ${data}`);
  });

  pythonProcess.on("close", (code) => {
    //   console.log(`Python process exited with code ${code}`);
    //   console.log(globalThis.output);
  });

  // Wait for the Python process to exit and the output to be fully processed
  pythonProcess.on("exit", () => {
    //   console.log("Python process has exited");
    //   console.log(globalThis.output);
  });

  // Use a promise to wait for the Python process to exit and the output to be fully processed
  const waitForOutput = new Promise((resolve) => {
    pythonProcess.on("exit", () => {
      resolve();
    });
  });

  // Access the global "output" array after the promise has resolved
  // waitForOutput.then(() => {
  //   //   console.log(globalThis.output);
  //   console.log(compare_output(globalThis.output, output));
  //   // console.log(global.tot);
  // });

  // console.log(logger);
}

function compare_output(lines1, lines2) {
  let result = 1;

  // Split the contents of each file into an array of lines
  //   const lines1 = file1.trim().split("\n");
  //   const lines2 = file2.trim().split("\n");

  // Compare the two arrays of lines
  if (lines1.length !== lines2.length) {
    result = 0;
  } else {
    for (let i = 0; i < lines1.length; i++) {
      if (lines1[i].trim() !== lines2[i].trim()) {
        result = 0;
        break;
      }
    }
  }
  return result;
}

function test(que_num, submitted_file) {
  const inputs = fs
    .readFileSync(`./Test-Checker/${que_num}/input.in`, "utf-8")
    .trim()
    .split("\n");
  const outputs = fs
    .readFileSync(`./Test-Checker/${que_num}/output.out`, "utf-8")
    .trim()
    .split("\n");

  grab_output(inputs, que_num, submitted_file);
  res = fs
    .readFileSync(`./Test-Checker/${que_num}/res.txt`, "utf-8")
    .trim()
    .split("\n");
  return compare_output(outputs, res);

  // Print the log messages array
  // console.log(logMessages);
}

// var answer = test(1, "solution.py");
// console.log(answer);

module.exports = {
  test,
};
// grab_output(inputs);

// console.log(compare_output(grab_output()));
