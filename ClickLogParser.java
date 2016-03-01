import java.io.BufferedInputStream;
import java.io.DataInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.PrintStream;
import java.io.FileOutputStream;
import java.text.DecimalFormat;


public class ClickLogParser {

	public static void main(String[] args) throws Exception {
		
		if (args.length < 1){
			System.out.println("Usage: java ClickLogParser <file_name>");
			return;
		}

		File logFile =  new File(args[0]);
		File logFile_out =  new File(args[1]);	
		DataInputStream ds = null;
		PrintStream dout = null;
		
		DecimalFormat format = new DecimalFormat();
		format.setMaximumIntegerDigits(8);
		format.setMinimumIntegerDigits(8);
		format.setGroupingUsed(false);
		try {
			ds = new DataInputStream(new BufferedInputStream(new FileInputStream(logFile)));
			dout =  new PrintStream(new FileOutputStream(logFile_out));
			long ts;
			long msg;
			while (true){
				StringBuilder sb = new StringBuilder();
				ts = ds.readLong();
				msg = ds.readLong();
				sb.append(ts);
				sb.append('\t');
				sb.append(format.format(msg));
				dout.println(sb.toString());
				dout.flush();
			}
		} catch (Exception e) {
		} finally{
			try {
				ds.close();
				dout.close();
			} catch (Exception e) {}
		}
		
	}

}
